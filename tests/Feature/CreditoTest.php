<?php

namespace Tests\Feature;

use App\Jobs\AplicarCreditoPendente;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreditoTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_sem_role_financeiro_nao_pode_aplicar_credito(): void
    {
        $operacional = User::factory()->create(['role' => 'operacional']);
        $cliente     = Cliente::factory()->create();

        $this->actingAs($operacional)
            ->postJson("/api/v1/clientes/{$cliente->id}/aplicar-credito", ['valor' => 100])
            ->assertStatus(403);
    }

    public function test_valor_negativo_retorna_422(): void
    {
        $financeiro = User::factory()->create(['role' => 'financeiro']);
        $cliente    = Cliente::factory()->create();

        $this->actingAs($financeiro)
            ->postJson("/api/v1/clientes/{$cliente->id}/aplicar-credito", ['valor' => -50])
            ->assertStatus(422);
    }

    public function test_valor_zero_retorna_422(): void
    {
        $financeiro = User::factory()->create(['role' => 'financeiro']);
        $cliente    = Cliente::factory()->create();

        $this->actingAs($financeiro)
            ->postJson("/api/v1/clientes/{$cliente->id}/aplicar-credito", ['valor' => 0])
            ->assertStatus(422);
    }

    public function test_financeiro_pode_aplicar_credito_e_saldo_e_atualizado(): void
    {
        Queue::fake();

        $financeiro = User::factory()->create(['role' => 'financeiro']);
        $cliente    = Cliente::factory()->create(['saldo_credito' => 0]);

        $this->actingAs($financeiro)
            ->postJson("/api/v1/clientes/{$cliente->id}/aplicar-credito", ['valor' => 250.00])
            ->assertOk()
            ->assertJsonFragment(['saldo_credito' => '250.00']);

        $this->assertDatabaseHas('clientes', [
            'id'            => $cliente->id,
            'saldo_credito' => 250.00,
        ]);
    }

    public function test_aplicar_credito_dispara_job_assincrono(): void
    {
        Queue::fake();

        $financeiro = User::factory()->create(['role' => 'financeiro']);
        $cliente    = Cliente::factory()->create(['saldo_credito' => 0]);

        $this->actingAs($financeiro)
            ->postJson("/api/v1/clientes/{$cliente->id}/aplicar-credito", ['valor' => 100]);

        Queue::assertPushed(AplicarCreditoPendente::class, function ($job) use ($cliente) {
            return $job->clienteId === $cliente->id;
        });
    }

    public function test_admin_tambem_pode_aplicar_credito(): void
    {
        Queue::fake();

        $admin   = User::factory()->create(['role' => 'admin']);
        $cliente = Cliente::factory()->create(['saldo_credito' => 100]);

        $this->actingAs($admin)
            ->postJson("/api/v1/clientes/{$cliente->id}/aplicar-credito", ['valor' => 50])
            ->assertOk();

        $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'saldo_credito' => 150.00]);
    }
}
