<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContratoRequest;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $contratos = Contrato::query()
            ->with(['cliente:id,nome,documento', 'itens'])
            ->when($request->filled('cliente_id'), fn ($q) => $q->where('cliente_id', $request->input('cliente_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 20));

        return response()->json($contratos);
    }

    public function show(Contrato $contrato): JsonResponse
    {
        return response()->json(
            $contrato->load(['cliente', 'itens', 'cobrancas', 'ordensServico.responsavel'])
        );
    }

    public function store(StoreContratoRequest $request): JsonResponse
    {
        $contrato = Contrato::create($request->safe()->except('itens'));

        foreach ($request->input('itens', []) as $item) {
            $contrato->itens()->create($item);
        }

        return response()->json($contrato->load('itens'), 201);
    }

    public function update(StoreContratoRequest $request, Contrato $contrato): JsonResponse
    {
        $contrato->update($request->safe()->except('itens'));

        if ($request->has('itens')) {
            $contrato->itens()->delete();
            foreach ($request->input('itens') as $item) {
                $contrato->itens()->create($item);
            }
        }

        return response()->json($contrato->load('itens'));
    }

    public function destroy(Contrato $contrato): JsonResponse
    {
        $contrato->delete();

        return response()->json(['message' => 'Contrato removido com sucesso.']);
    }
}
