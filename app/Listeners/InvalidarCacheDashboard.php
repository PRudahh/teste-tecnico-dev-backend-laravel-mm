<?php

namespace App\Listeners;

use App\Events\CobrancaStatusAlterado;
use App\Services\DashboardService;

class InvalidarCacheDashboard
{
    public function __construct(private DashboardService $dashboardService) {}

    public function handle(CobrancaStatusAlterado $event): void
    {
        $this->dashboardService->invalidarCache();
    }
}