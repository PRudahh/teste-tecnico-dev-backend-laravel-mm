<?php

namespace App\Services;

use App\Domain\OrdemServico\StatusOrdemServico;
use App\Models\HistoricoStatusOrdem;
use App\Models\OrdemServico;
use Illuminate\Support\Facades\DB;

class OrdemServicoService
{
    /**
     * Muda o status de uma OS e registra auditoria.
     * Toda a operação é atômica.
     */
    public function mudarStatus(
        OrdemServico $os,
        string $novoStatus,
        int $usuarioId,
        ?string $observacao = null
    ): OrdemServico {
        return DB::transaction(function () use ($os, $novoStatus, $usuarioId, $observacao) {
            StatusOrdemServico::validarTransicao($os->status, $novoStatus);

            $statusAnterior = $os->status;
            $os->status = $novoStatus;
            $os->save();

            HistoricoStatusOrdem::create([
                'ordem_servico_id' => $os->id,
                'usuario_id'       => $usuarioId,
                'status_anterior'  => $statusAnterior,
                'status_novo'      => $novoStatus,
                'observacao'       => $observacao,
                'created_at'       => now(),
            ]);

            return $os->fresh(['historico.usuario']);
        });
    }
}
