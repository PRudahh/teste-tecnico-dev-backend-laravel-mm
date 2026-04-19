<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    const CACHE_KEY = 'dashboard_financeiro';
    const CACHE_TTL = 300;

    public function getDados(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->consultarDados();
        });
    }

    public function invalidarCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function consultarDados(): array
    {
        $agora             = now();
        $inicioMesAtual    = $agora->copy()->startOfMonth();
        $fimMesAtual       = $agora->copy()->endOfMonth();
        $inicioMesAnterior = $agora->copy()->subMonth()->startOfMonth();
        $fimMesAnterior    = $agora->copy()->subMonth()->endOfMonth();

        $faturamento = DB::selectOne("
            SELECT
                COALESCE(SUM(CASE
                    WHEN data_referencia BETWEEN ? AND ? THEN valor
                    ELSE 0
                END), 0) AS mes_atual,
                COALESCE(SUM(CASE
                    WHEN data_referencia BETWEEN ? AND ? THEN valor
                    ELSE 0
                END), 0) AS mes_anterior
            FROM cobrancas
            WHERE status IN ('pago', 'pago_parcial')
        ", [
            $inicioMesAtual->toDateString(),
            $fimMesAtual->toDateString(),
            $inicioMesAnterior->toDateString(),
            $fimMesAnterior->toDateString(),
        ]);

        $mesAtual   = (float) $faturamento->mes_atual;
        $mesAnterior = (float) $faturamento->mes_anterior;
        $variacaoPercentual = $mesAnterior > 0
            ? round((($mesAtual - $mesAnterior) / $mesAnterior) * 100, 2)
            : ($mesAtual > 0 ? 100 : 0);

        $totaisStatus = DB::selectOne("
            SELECT
                COALESCE(SUM(CASE WHEN status IN ('aguardando_pagamento', 'pago_parcial') THEN valor - credito_aplicado - valor_pago ELSE 0 END), 0) AS total_em_aberto,
                COALESCE(SUM(CASE WHEN status = 'inadimplente' THEN valor - credito_aplicado - valor_pago ELSE 0 END), 0) AS total_inadimplente
            FROM cobrancas
        ");

        $topClientes = DB::select("
            SELECT
                c.id,
                c.nome,
                c.documento,
                SUM(ic.quantidade * ic.valor_unitario) AS valor_total_contratos
            FROM clientes c
            INNER JOIN contratos ct ON ct.cliente_id = c.id AND ct.status = 'ativo'
            INNER JOIN itens_contrato ic ON ic.contrato_id = ct.id
            GROUP BY c.id, c.nome, c.documento
            ORDER BY valor_total_contratos DESC
            LIMIT 5
        ");

        $distribuicaoOS = DB::select("
            SELECT status, COUNT(*) AS total
            FROM ordens_servico
            GROUP BY status
        ");

        return [
            'faturamento' => [
                'mes_atual'           => $mesAtual,
                'mes_anterior'        => $mesAnterior,
                'variacao_percentual' => $variacaoPercentual,
            ],
            'total_em_aberto'    => (float) $totaisStatus->total_em_aberto,
            'total_inadimplente' => (float) $totaisStatus->total_inadimplente,
            'top_clientes'       => collect($topClientes)->map(fn ($c) => [
                'id'                    => $c->id,
                'nome'                  => $c->nome,
                'documento'             => $c->documento,
                'valor_total_contratos' => (float) $c->valor_total_contratos,
            ])->values(),
            'distribuicao_ordens_servico' => collect($distribuicaoOS)
                ->mapWithKeys(fn ($os) => [$os->status => (int) $os->total]),
        ];
    }
}