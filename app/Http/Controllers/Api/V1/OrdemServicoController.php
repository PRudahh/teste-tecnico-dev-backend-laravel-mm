<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AtualizarStatusOrdemRequest;
use App\Http\Requests\StoreOrdemServicoRequest;
use App\Models\OrdemServico;
use App\Services\OrdemServicoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdemServicoController extends Controller
{
    public function __construct(private OrdemServicoService $ordemService) {}

    public function index(Request $request): JsonResponse
    {
        $ordens = OrdemServico::query()
            ->with(['contrato.cliente:id,nome', 'responsavel:id,name,role'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('contrato_id'), fn ($q) => $q->where('contrato_id', $request->input('contrato_id')))
            ->when($request->filled('responsavel_id'), fn ($q) => $q->where('responsavel_id', $request->input('responsavel_id')))
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 20));

        return response()->json($ordens);
    }

    public function show(OrdemServico $ordemServico): JsonResponse
    {
        return response()->json(
            $ordemServico->load(['contrato.cliente', 'responsavel', 'historico.usuario:id,name'])
        );
    }

    public function store(StoreOrdemServicoRequest $request): JsonResponse
    {
        $os = OrdemServico::create($request->validated());

        return response()->json($os->load(['responsavel:id,name', 'contrato:id,descricao']), 201);
    }

    public function update(StoreOrdemServicoRequest $request, OrdemServico $ordemServico): JsonResponse
    {
        $ordemServico->update($request->validated());

        return response()->json($ordemServico->load(['responsavel:id,name', 'contrato:id,descricao']));
    }

    /**
     * PATCH /api/v1/ordens-servico/{id}/status
     * Muda status com auditoria automática.
     */
    public function atualizarStatus(AtualizarStatusOrdemRequest $request, OrdemServico $ordemServico): JsonResponse
    {
        $os = $this->ordemService->mudarStatus(
            $ordemServico,
            $request->input('status'),
            $request->user()->id,
            $request->input('observacao')
        );

        return response()->json($os);
    }
}
