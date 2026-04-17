<?php

namespace App\Events;

use App\Models\Cobranca;
use Illuminate\Foundation\Events\Dispatchable;

class CobrancaStatusAlterado
{
    use Dispatchable;

    public function __construct(
        public readonly Cobranca $cobranca,
        public readonly string $statusAnterior,
    ) {}
}