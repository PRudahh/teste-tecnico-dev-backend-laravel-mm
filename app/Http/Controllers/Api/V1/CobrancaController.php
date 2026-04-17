<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Cobranca\StatusCobranca;
use App\Http\Controllers\Controller;
use App\Http\Requests\AtualizarStatusCobrancaRequest;
use App\Http\Requests\ListarCobrancasRequest;
use App\Models\Cobranca;
use App\Services\CobrancaService;
use Illuminate\Http\JsonResponse;

class CobrancaController extends Controller
{
    public function __construct(private CobrancaService $cobrancaService) {}

    /**
     * GET /api/v1/cobrancas
     *
     * Suporta:
     * - Filtro por status[] (múltiplos)
     * - Filtro por data_inicio e data_fim (data_referencia)
     * - Busca por nome ou documento do cliente
     * - Ordenação com whitelist
     * - Paginação por cursor (padrão) ou offset (?page=N)
     */
    public function index(ListarCobrancasRequest $request): JsonResponse
    {
        $query = Cobranca::query()
            ->with(['cliente:id,nome,documento', 'contrato:id,descricao'])
            ->select('cobrancas.*');

        // ── Filtro por status (múltiplos) ──────────────────────────────────
        if ($request->filled('status')) {
            $query->whereIn('status', $request->input('status'));
        }

        // ── Filtro por intervalo de datas de referência ────────────────────
        if ($request->filled('data_inicio')) {
            $query->where('data_referencia', '>=', $request->input('data_inicio'));
        }
        if ($request->filled('data_fim')) {
            $query->where('data_referencia', '<=', $request->input('data_fim'));
        }

        // ── Busca por nome ou documento do cliente ─────────────────────────
        if ($request->filled('busca')) {
            $busca = '%' . $request->input('busca') . '%';
            $query->whereHas('cliente', function ($q) use ($busca) {
                $q->where('nome', 'like', $busca)
                  ->orWhere('documento', 'like', $busca);
            });
        }

        // ── Ordenação (whitelist) ──────────────────────────────────────────
        $camposPermitidos = StatusCobranca::ordenaveisWhitelist();
        $orderBy    = $request->input('order_by', 'data_vencimento');
        $orderDir   = $request->input('order_dir', 'desc');

        if (in_array($orderBy, $camposPermitidos, true)) {
            $query->orderBy("cobrancas.{$orderBy}", $orderDir === 'asc' ? 'asc' : 'desc');
        }

        // ── Paginação: cursor (padrão eficiente) ou offset (?page=N) ───────
        if ($request->filled('page')) {
            $paginator = $query->paginate(
                perPage: $request->integer('per_page', 20)
            );
            return response()->json($paginator);
        }

        $paginator = $query->cursorPaginate(
            perPage: $request->integer('per_page', 20)
        );

        return response()->json($paginator);
    }

    /**
     * PATCH /api/v1/cobrancas/{id}/status
     */
    public function atualizarStatus(AtualizarStatusCobrancaRequest $request, Cobranca $cobranca): JsonResponse
    {
        $cobranca = $this->cobrancaService->mudarStatus(
            $cobranca,
            $request->input('status'),
            $request->only('motivo_cancelamento')
        );

        return response()->json($cobranca->load(['cliente', 'contrato']));
    }
}
