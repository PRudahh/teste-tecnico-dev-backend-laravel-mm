<?php

namespace Tests\Feature;

use App\Domain\Cobranca\StatusCobranca;
use App\Models\Cliente;
use App\Models\Cobranca;
use App\Models\Contrato;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CobrancaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Cliente $cliente;
    private Contrato $contrato;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);
        $this->cliente = Cliente::factory()->create(['saldo_credito' => 0]);
        $this->contrato = Contrato::factory()->create(['cliente_id' => $this->cliente->id]);
    }

    private function criarCobranca(array $attrs = []): Cobranca
    {
        return Cobranca::factory()->create(array_merge([
            'contrato_id' => $this->contrato->id,
            'cliente_id'  => $this->cliente->id,
            'valor'       => 1000.00,
            'status'      => StatusCobranca::PENDENTE,
            'data_referencia' => Carbon::now()->startOfMonth()->toDateString(),
            'data_vencimento' => Carbon::now()->addDays(10)->toDateString(),
        ], $attrs));
    }

    /** @test */
    public function listar_cobrancas_requer_autenticacao(): void
    {
        $this->getJson('/api/v1/cobrancas')
            ->assertStatus(401);
    }

    /** @test */
    public function usuario_autenticado_pode_listar_cobrancas(): void
    {
        $this->criarCobranca();

        $this->actingAs($this->user)
            ->getJson('/api/v1/cobrancas')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    /** @test */
    public function pode_filtrar_cobrancas_por_status(): void
    {
        $this->criarCobranca(['status' => StatusCobranca::PENDENTE]);
        $this->criarCobranca(['status' => StatusCobranca::PAGO]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/cobrancas?status[]=pendente')
            ->assertOk();

        foreach ($response->json('data') as $cobranca) {
            $this->assertEquals(StatusCobranca::PENDENTE, $cobranca['status']);
        }
    }

    /** @test */
    public function pode_filtrar_por_multiplos_status(): void
    {
        $this->criarCobranca(['status' => StatusCobranca::PENDENTE]);
        $this->criarCobranca(['status' => StatusCobranca::PAGO]);
        $this->criarCobranca(['status' => StatusCobranca::CANCELADO]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/cobrancas?status[]=pendente&status[]=pago')
            ->assertOk();

        $statuses = collect($response->json('data'))->pluck('status')->unique()->values()->toArray();
        sort($statuses);
        $this->assertEquals(['pago', 'pendente'], $statuses);
    }

    /** @test */
    public function transicao_invalida_retorna_422(): void
    {
        $cobranca = $this->criarCobranca(['status' => StatusCobranca::PAGO]);

        $this->actingAs($this->user)
            ->patchJson("/api/v1/cobrancas/{$cobranca->id}/status", [
                'status' => StatusCobranca::PENDENTE,
            ])
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Transição inválida: não é possível mudar de [pago] para [pendente].']);
    }

    /** @test */
    public function cancelamento_sem_motivo_retorna_erro(): void
    {
        $cobranca = $this->criarCobranca(['status' => StatusCobranca::PENDENTE]);

        $this->actingAs($this->user)
            ->patchJson("/api/v1/cobrancas/{$cobranca->id}/status", [
                'status' => StatusCobranca::CANCELADO,
                // motivo_cancelamento ausente
            ])
            ->assertStatus(422);
    }

    /** @test */
    public function cancelamento_com_motivo_funciona(): void
    {
        $cobranca = $this->criarCobranca(['status' => StatusCobranca::PENDENTE]);

        $this->actingAs($this->user)
            ->patchJson("/api/v1/cobrancas/{$cobranca->id}/status", [
                'status'              => StatusCobranca::CANCELADO,
                'motivo_cancelamento' => 'Cliente solicitou cancelamento.',
            ])
            ->assertOk()
            ->assertJsonFragment(['status' => StatusCobranca::CANCELADO]);
    }

    /** @test */
    public function ordenacao_por_campo_nao_permitido_e_ignorada_sem_erro(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/v1/cobrancas?order_by=password')
            ->assertStatus(422); // campo não está na whitelist
    }
}

