<?php

namespace App\Services;

use App\Domain\Cobranca\StatusCobranca;
use App\Events\CobrancaStatusAlterado;
use App\Models\Cobranca;
use App\Models\Cliente;
use App\Models\Contrato;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CobrancaService
{
    public function __construct(private CreditoService $creditoService) {}

    /**
     * Gera cobrança mensal para um contrato.
     * O valor é calculado a partir dos itens do contrato.
     */
    public function gerarCobrancaMensal(Contrato $contrato, Carbon $referencia): Cobranca
    {
        $contrato->loadMissing('itens');

        $valor = $contrato->itens->sum(
            fn ($item) => $item->quantidade * $item->valor_unitario
        );

        if ($valor <= 0) {
            throw new DomainException("Contrato #{$contrato->id} não possui itens com valor.");
        }

        $dataVencimento = $referencia->copy()
            ->day($contrato->dia_vencimento);

        return Cobranca::create([
            'contrato_id'    => $contrato->id,
            'cliente_id'     => $contrato->cliente_id,
            'valor'          => $valor,
            'valor_pago'     => 0,
            'credito_aplicado' => 0,
            'status'         => StatusCobranca::PENDENTE,
            'data_referencia' => $referencia->startOfMonth()->toDateString(),
            'data_vencimento' => $dataVencimento->toDateString(),
        ]);
    }

    /**
     * Transiciona o status de uma cobrança com todas as regras de negócio.
     * Toda transição é atômica (usa DB transaction).
     */
    public function mudarStatus(
        Cobranca $cobranca,
        string $novoStatus,
        array $dados = []
    ): Cobranca {
        return DB::transaction(function () use ($cobranca, $novoStatus, $dados) {

            // 1. Valida a transição via Value Object — lança DomainException se inválida
            StatusCobranca::validarTransicao($cobranca->status, $novoStatus);

            // 2. Regra: só pode ir para inadimplente se já passou da data de vencimento
            if ($novoStatus === StatusCobranca::INADIMPLENTE && !$cobranca->estaVencida()) {
                throw new DomainException(
                    "A cobrança só pode ser marcada como inadimplente após a data de vencimento ({$cobranca->data_vencimento->format('d/m/Y')})."
                );
            }

            // 3. Regra: cancelamento exige motivo
            if ($novoStatus === StatusCobranca::CANCELADO) {
                if (empty($dados['motivo_cancelamento'])) {
                    throw new DomainException('O motivo do cancelamento é obrigatório.');
                }
                $cobranca->motivo_cancelamento = $dados['motivo_cancelamento'];
            }

            // 4. Regra: ao entrar em pago/pago_parcial, aplica saldo de crédito disponível
            if (in_array($novoStatus, [StatusCobranca::AGUARDANDO_PAGAMENTO, StatusCobranca::PAGO])) {
                $cobranca->refresh(); // garante dados frescos
                $cliente = $cobranca->cliente;

                if ($cliente->temSaldoCredito() && $cobranca->valorRestante() > 0) {
                    $novoStatus = $this->creditoService->aplicarCreditoNaCobranca(
                        $cobranca,
                        $cliente
                    );
                }
            }

            $statusAnterior = $cobranca->status;
            $cobranca->status = $novoStatus;
            $cobranca->save();

            // 5. Dispara evento para invalidar cache do dashboard
            event(new CobrancaStatusAlterado($cobranca, $statusAnterior));

            Log::info('Cobrança status alterado', [
                'cobranca_id'    => $cobranca->id,
                'status_anterior' => $statusAnterior,
                'status_novo'    => $novoStatus,
            ]);

            return $cobranca->fresh();
        });
    }
}
