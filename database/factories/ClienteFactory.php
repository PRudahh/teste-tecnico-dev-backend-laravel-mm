<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome'          => $this->faker->company(),
            'documento'     => $this->faker->unique()->numerify('##.###.###/0001-##'),
            'email'         => $this->faker->unique()->companyEmail(),
            'telefone'      => $this->faker->phoneNumber(),
            'saldo_credito' => 0,
        ];
    }
}
