<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Administrador',
                'email'    => 'admin@agency.com',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ],
            [
                'name'     => 'Financeiro',
                'email'    => 'financeiro@agency.com',
                'password' => Hash::make('password'),
                'role'     => 'financeiro',
            ],
            [
                'name'     => 'Operacional',
                'email'    => 'operacional@agency.com',
                'password' => Hash::make('password'),
                'role'     => 'operacional',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['email' => $user['email']], $user);
        }

        $this->command->info('Usuários criados: admin@agency.com, financeiro@agency.com, operacional@agency.com (senha: password)');
    }
}
