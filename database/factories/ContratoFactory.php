<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContratoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id'     => Cliente::factory(),
            'descricao'      => $this->faker->sentence(4),
            'data_inicio'    => now()->subMonths(3)->toDateString(),
            'status'         => 'ativo',
            'dia_vencimento' => 10,
        ];
    }
}
