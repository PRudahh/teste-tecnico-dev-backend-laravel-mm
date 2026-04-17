<?php

namespace App\Services;

use App\Domain\Cobranca\StatusCobranca;
use App\Models\Cobranca;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditoService
{
    /**
     * Aplica crédito do cliente a uma cobrança específica.
     * Retorna o novo status da cobrança após a aplicação.
     *
     * Deve ser chamado DENTRO de uma DB::transaction existente.
     */
    public function aplicarCreditoNaCobranca(Cobranca $cobranca, Cliente $cliente): string
    {
        $saldoDisponivel = $cliente->saldo_credito;
        $valorRestante   = $cobranca->valorRestante();

        if ($saldoDisponivel <= 0 || $valorRestante <= 0) {
            return $cobranca->status;
        }

        $creditoAplicar = min($saldoDisponivel, $valorRestante);

        // Atualiza crédito na cobrança
        $cobranca->credito_aplicado += $creditoAplicar;

        // Debita saldo do cliente com lock para evitar race condition
        Cliente::where('id', $cliente->id)
            ->lockForUpdate()
            ->first()
            ->decrement('saldo_credito', $creditoAplicar);

        $cliente->refresh();

        // Define novo status
        $novoStatus = $cobranca->credito_aplicado >= $cobranca->valor
            ? StatusCobranca::PAGO
            : StatusCobranca::PAGO_PARCIAL;

        Log::info('Crédito aplicado em cobrança', [
            'cliente_id'       => $cliente->id,
            'cobranca_id'      => $cobranca->id,
            'credito_aplicado' => $creditoAplicar,
            'novo_status'      => $novoStatus,
            'saldo_restante'   => $cliente->saldo_credito,
        ]);

        return $novoStatus;
    }

    /**
     * Adiciona crédito manual ao saldo do cliente.
     * Apenas usuários com role financeiro podem chamar este método.
     */
    public function adicionarCredito(Cliente $cliente, float $valor, int $usuarioId): Cliente
    {
        return DB::transaction(function () use ($cliente, $valor, $usuarioId) {
            $saldoAnterior = $cliente->saldo_credito;

            $cliente->lockForUpdate();
            $cliente->increment('saldo_credito', $valor);
            $cliente->refresh();

            Log::info('Crédito adicionado manualmente', [
                'cliente_id'     => $cliente->id,
                'usuario_id'     => $usuarioId,
                'valor'          => $valor,
                'saldo_anterior' => $saldoAnterior,
                'saldo_novo'     => $cliente->saldo_credito,
            ]);

            return $cliente;
        });
    }
}
