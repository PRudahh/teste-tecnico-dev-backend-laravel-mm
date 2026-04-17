<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    /**
     * GET /api/v1/dashboard
     *
     * Retorna dados financeiros consolidados com cache de 5 minutos.
     * Cache invalidado automaticamente ao mudar status de cobranças.
     */
    public function index(): JsonResponse
    {
        return response()->json($this->dashboardService->getDados());
    }
}
