<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AplicarCreditoRequest;
use App\Http\Requests\StoreClienteRequest;
use App\Jobs\AplicarCreditoPendente;
use App\Models\Cliente;
use App\Services\CreditoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function __construct(private CreditoService $creditoService) {}

    public function index(Request $request): JsonResponse
    {
        $clientes = Cliente::query()
            ->when($request->filled('busca'), function ($q) use ($request) {
                $busca = '%' . $request->input('busca') . '%';
                $q->where('nome', 'like', $busca)
                  ->orWhere('documento', 'like', $busca);
            })
            ->orderBy('nome')
            ->paginate($request->integer('per_page', 20));

        return response()->json($clientes);
    }

    public function show(Cliente $cliente): JsonResponse
    {
        return response()->json(
            $cliente->load(['contratos' => fn ($q) => $q->with('itens')])
        );
    }

    public function store(StoreClienteRequest $request): JsonResponse
    {
        $cliente = Cliente::create($request->validated());

        return response()->json($cliente, 201);
    }

    public function update(StoreClienteRequest $request, Cliente $cliente): JsonResponse
    {
        $cliente->update($request->validated());

        return response()->json($cliente);
    }

    public function destroy(Cliente $cliente): JsonResponse
    {
        $cliente->delete();

        return response()->json(['message' => 'Cliente removido com sucesso.']);
    }

    /**
     * POST /api/v1/clientes/{id}/aplicar-credito
     *
     * Aplica crédito manual ao saldo do cliente.
     * Apenas usuários com role financeiro ou admin.
     */
    public function aplicarCredito(AplicarCreditoRequest $request, Cliente $cliente): JsonResponse
    {
        $valor = $request->input('valor');

        $cliente = $this->creditoService->adicionarCredito(
            $cliente,
            $valor,
            $request->user()->id
        );

        // Dispara job assíncrono para tentar aplicar crédito nas cobranças pendentes
        AplicarCreditoPendente::dispatch($cliente->id);

        return response()->json([
            'message'        => 'Crédito aplicado com sucesso.',
            'saldo_credito'  => $cliente->saldo_credito,
        ]);
    }
}
