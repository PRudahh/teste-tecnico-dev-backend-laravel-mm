<?php

namespace Database\Factories;

use App\Domain\Cobranca\StatusCobranca;
use App\Models\Cliente;
use App\Models\Contrato;
use Illuminate\Database\Eloquent\Factories\Factory;

class CobrancaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'contrato_id'      => Contrato::factory(),
            'cliente_id'       => Cliente::factory(),
            'valor'            => $this->faker->randomFloat(2, 500, 5000),
            'valor_pago'       => 0,
            'credito_aplicado' => 0,
            'status'           => StatusCobranca::PENDENTE,
            'data_referencia'  => now()->startOfMonth()->toDateString(),
            'data_vencimento'  => now()->day(10)->toDateString(),
        ];
    }
}
