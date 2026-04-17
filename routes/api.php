<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClienteController;
use App\Http\Controllers\Api\V1\CobrancaController;
use App\Http\Controllers\Api\V1\ContratoController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\OrdemServicoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Agency System
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── Autenticação (pública) ────────────────────────────────────────────
    Route::post('/auth/login', [AuthController::class, 'login']);

    // ── Rotas protegidas por Sanctum ──────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // ── Dashboard (cache 5min, invalidado por evento) ─────────────────
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // ── Cobranças — com rate limit de 20 req/min por usuário ──────────
        Route::middleware('throttle:cobrancas')->group(function () {
            Route::get('/cobrancas', [CobrancaController::class, 'index']);
        });

        // Mudança de status de cobranças (sem rate limit específico)
        Route::patch('/cobrancas/{cobranca}/status', [CobrancaController::class, 'atualizarStatus']);

        // ── Clientes ──────────────────────────────────────────────────────
        Route::apiResource('clientes', ClienteController::class);

        // Aplicar crédito — apenas financeiro/admin (autorização no FormRequest)
        Route::post('/clientes/{cliente}/aplicar-credito', [ClienteController::class, 'aplicarCredito']);

        // ── Contratos ─────────────────────────────────────────────────────
        Route::apiResource('contratos', ContratoController::class);

        // ── Ordens de Serviço ─────────────────────────────────────────────
        Route::apiResource('ordens-servico', OrdemServicoController::class);
        Route::patch('/ordens-servico/{ordemServico}/status', [OrdemServicoController::class, 'atualizarStatus']);
    });
});
