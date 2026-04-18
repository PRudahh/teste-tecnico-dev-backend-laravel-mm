<?php

namespace App\Jobs;

use App\Models\Cliente;
use App\Services\CobrancaService;
use App\Services\CreditoService;
use App\Domain\Cobranca\StatusCobranca;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AplicarCreditoPendente implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Máximo de tentativas (1 inicial + 3 retries = 4 total).
     */
    public int $tries = 4;

    /**
     * Backoff exponencial: 1min, 5min, 30min entre tentativas.
     * (O Laravel usa este array como tempo de espera por attempt)
     */
    public array $backoff = [60, 300, 1800];

    /**
     * Timeout por execução do job.
     */
    public int $timeout = 60;

    public function __construct(public readonly int $clienteId) {}

    public function handle(CreditoService $creditoService, CobrancaService $cobrancaService): void
    {
        /**
         * IDEMPOTÊNCIA via distributed lock.
         *
         * Se um segundo job for disparado para o mesmo cliente enquanto o
         * primeiro ainda está sendo processado, o lock não será adquirido
         * e o job encerra silenciosamente — sem dupla aplicação de crédito.
         *
         * Usamos Cache::lock com driver 'database' (ou redis), que garante
         * atomicidade mesmo com múltiplos workers rodando em paralelo.
         */
        $lockKey = "aplicar_credito_cliente_{$this->clienteId}";
        $lock = Cache::lock($lockKey, 120); // lock por 2 minutos

        if (!$lock->get()) {
            Log::info("Job AplicarCreditoPendente: lock não adquirido para cliente {$this->clienteId}. Outro job em execução — encerrando silenciosamente.");
            return;
        }

        try {
            $cliente = Cliente::lockForUpdate()->find($this->clienteId);

            if (!$cliente) {
                Log::warning("Job AplicarCreditoPendente: cliente {$this->clienteId} não encontrado.");
                return;
            }

            if (!$cliente->temSaldoCredito()) {
                Log::info("Job AplicarCreditoPendente: cliente {$this->clienteId} sem saldo de crédito — encerrando silenciosamente.");
                return;
            }

            // Busca cobranças pendentes ordenadas da mais antiga para a mais recente
            $cobrancasPendentes = $cliente->cobrancasPendentes()->get();

            if ($cobrancasPendentes->isEmpty()) {
                Log::info("Job AplicarCreditoPendente: cliente {$this->clienteId} sem cobranças pendentes — encerrando silenciosamente.");
                return;
            }

            DB::transaction(function () use ($cliente, $cobrancasPendentes, $creditoService, $cobrancaService) {
                foreach ($cobrancasPendentes as $cobranca) {
                    $cliente->refresh();

                    if (!$cliente->temSaldoCredito()) {
                        break; // Saldo zerou, para de processar
                    }

                    $novoStatus = $creditoService->aplicarCreditoNaCobranca($cobranca, $cliente);

                    $cobranca->status = $novoStatus;
                    $cobranca->save();

                    Log::info("Job AplicarCreditoPendente: crédito aplicado na cobrança {$cobranca->id}", [
                        'cliente_id' => $cliente->id,
                        'novo_status' => $novoStatus,
                    ]);
                }
            });

        } finally {
            $lock->release();
        }
    }

    /**
     * Chamado após todas as tentativas falharem.
     * Registra falha definitiva no log de erros.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Job AplicarCreditoPendente falhou definitivamente', [
            'cliente_id' => $this->clienteId,
            'erro'       => $exception->getMessage(),
            'trace'      => $exception->getTraceAsString(),
        ]);
    }
}
