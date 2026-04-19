<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);

        $middleware->api(append: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (\DomainException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                $previous = $e->getPrevious();

                if ($previous instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $modelo = class_basename($previous->getModel());
                    $nomes = [
                        'Cliente'      => 'Cliente',
                        'Contrato'     => 'Contrato',
                        'Cobranca'     => 'Cobrança',
                        'OrdemServico' => 'Ordem de serviço',
                    ];
                    $nome = $nomes[$modelo] ?? $modelo;
                    return response()->json(['message' => "{$nome} não encontrado(a)."], 404);
                }

                return response()->json(['message' => 'Recurso não encontrado.'], 404);
            }
        });

    })
    ->withProviders([
        App\Providers\EventServiceProvider::class,
    ])
    ->create();