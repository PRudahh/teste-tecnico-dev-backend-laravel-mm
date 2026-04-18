<?php

namespace Tests\Unit\Domain;

use App\Domain\Cobranca\StatusCobranca;
use DomainException;
use PHPUnit\Framework\TestCase;

class StatusCobrancaTest extends TestCase
{
    /** @test */
    public function transicao_valida_nao_lanca_excecao(): void
    {
        $this->expectNotToPerformAssertions();

        StatusCobranca::validarTransicao(
            StatusCobranca::PENDENTE,
            StatusCobranca::AGUARDANDO_PAGAMENTO
        );
    }

    /** @test */
    public function transicao_invalida_lanca_domain_exception(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/Transição inválida/');

        StatusCobranca::validarTransicao(
            StatusCobranca::PAGO,
            StatusCobranca::PENDENTE
        );
    }

    /** @test */
    public function pago_e_status_terminal_sem_transicoes(): void
    {
        $this->expectException(DomainException::class);

        StatusCobranca::validarTransicao(StatusCobranca::PAGO, StatusCobranca::CANCELADO);
    }

    /** @test */
    public function cancelado_e_status_terminal(): void
    {
        $this->expectException(DomainException::class);

        StatusCobranca::validarTransicao(StatusCobranca::CANCELADO, StatusCobranca::PENDENTE);
    }

    /** @test */
    public function pendente_pode_ir_para_aguardando_pagamento(): void
    {
        $this->expectNotToPerformAssertions();
        StatusCobranca::validarTransicao(StatusCobranca::PENDENTE, StatusCobranca::AGUARDANDO_PAGAMENTO);
    }

    /** @test */
    public function aguardando_pagamento_pode_ir_para_inadimplente(): void
    {
        $this->expectNotToPerformAssertions();
        StatusCobranca::validarTransicao(StatusCobranca::AGUARDANDO_PAGAMENTO, StatusCobranca::INADIMPLENTE);
    }

    /** @test */
    public function cancelamento_exige_transicao_valida_antes(): void
    {
        $this->expectException(DomainException::class);

        // pago não pode cancelar
        StatusCobranca::validarTransicao(StatusCobranca::PAGO, StatusCobranca::CANCELADO);
    }

    /** @test */
    public function whitelist_de_ordenacao_contem_campos_esperados(): void
    {
        $whitelist = StatusCobranca::ordenaveisWhitelist();

        $this->assertContains('id', $whitelist);
        $this->assertContains('valor', $whitelist);
        $this->assertContains('data_vencimento', $whitelist);
        $this->assertNotContains('senha', $whitelist);
        $this->assertNotContains('password', $whitelist);
    }
}
