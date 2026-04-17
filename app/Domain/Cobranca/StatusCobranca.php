<?php

namespace App\Domain\Cobranca;

use DomainException;

/**
 * Value Object que encapsula as regras de transição de status de cobranças.
 * Transições inválidas lançam DomainException — nunca silenciosas.
 */
class StatusCobranca
{
    const PENDENTE              = 'pendente';
    const AGUARDANDO_PAGAMENTO  = 'aguardando_pagamento';
    const PAGO_PARCIAL          = 'pago_parcial';
    const PAGO                  = 'pago';
    const INADIMPLENTE          = 'inadimplente';
    const CANCELADO             = 'cancelado';

    /**
     * Mapa de transições permitidas: status_atual => [status_destino, ...]
     */
    private static array $transicoes = [
        self::PENDENTE => [
            self::AGUARDANDO_PAGAMENTO,
            self::CANCELADO,
        ],
        self::AGUARDANDO_PAGAMENTO => [
            self::PAGO,
            self::PAGO_PARCIAL,
            self::INADIMPLENTE,
            self::CANCELADO,
        ],
        self::PAGO_PARCIAL => [
            self::PAGO,
            self::INADIMPLENTE,
            self::CANCELADO,
        ],
        self::PAGO => [
            // Status terminal — nenhuma transição permitida
        ],
        self::INADIMPLENTE => [
            self::PAGO,
            self::PAGO_PARCIAL,
            self::CANCELADO,
        ],
        self::CANCELADO => [
            // Status terminal — nenhuma transição permitida
        ],
    ];

    /**
     * Valida e retorna o novo status se a transição for válida.
     * Lança DomainException se a transição for inválida.
     */
    public static function validarTransicao(string $statusAtual, string $novoStatus): void
    {
        if (!array_key_exists($statusAtual, self::$transicoes)) {
            throw new DomainException("Status atual inválido: [{$statusAtual}].");
        }

        if (!in_array($novoStatus, self::$transicoes[$statusAtual], true)) {
            throw new DomainException(
                "Transição inválida: não é possível mudar de [{$statusAtual}] para [{$novoStatus}]."
            );
        }
    }

    public static function todos(): array
    {
        return [
            self::PENDENTE,
            self::AGUARDANDO_PAGAMENTO,
            self::PAGO_PARCIAL,
            self::PAGO,
            self::INADIMPLENTE,
            self::CANCELADO,
        ];
    }

    public static function ordenaveisWhitelist(): array
    {
        return ['id', 'valor', 'data_referencia', 'data_vencimento', 'status', 'created_at'];
    }
}
