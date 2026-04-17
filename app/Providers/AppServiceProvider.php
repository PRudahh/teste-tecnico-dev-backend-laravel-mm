<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\CreditoService::class);
        $this->app->singleton(\App\Services\DashboardService::class);
        $this->app->singleton(\App\Services\OrdemServicoService::class);
        $this->app->singleton(\App\Services\CobrancaService::class, function ($app) {
            return new \App\Services\CobrancaService(
                $app->make(\App\Services\CreditoService::class)
            );
        });
    }

    public function boot(): void
    {
        $this->configurarRateLimiters();
    }

    private function configurarRateLimiters(): void
    {
        /*
         * Rate limit para listagem de cobranças: 20 req/min por usuário.
         * Conforme solicitado, usa o rate limiter NATIVO do Laravel —
         * não um middleware customizado do zero.
         */
        RateLimiter::for('cobrancas', function (Request $request) {
            return Limit::perMinute(20)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Muitas requisições. Limite: 20 por minuto.',
                    ], 429);
                });
        });

        // Rate limit geral da API
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });
    }
}
