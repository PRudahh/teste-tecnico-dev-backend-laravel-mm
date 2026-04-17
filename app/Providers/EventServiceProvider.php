<?php

namespace App\Providers;

use App\Events\CobrancaStatusAlterado;
use App\Listeners\InvalidarCacheDashboard;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CobrancaStatusAlterado::class => [
            InvalidarCacheDashboard::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}