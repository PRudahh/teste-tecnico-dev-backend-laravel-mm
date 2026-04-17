<?php

namespace App\Domain\OrdemServico;

use DomainException;

class StatusOrdemServico
{
    const ABERTA                = 'aberta';
    const EM_ANDAMENTO          = 'em_andamento';
    const AGUARDANDO_APROVACAO  = 'aguardando_aprovacao';
    const CONCLUIDA             = 'concluida';
    const CANCELADA             = 'cancelada';

    private static array $transicoes = [
        self::ABERTA => [
            self::EM_ANDAMENTO,
            self::CANCELADA,
        ],
        self::EM_ANDAMENTO => [
            self::AGUARDANDO_APROVACAO,
            self::CONCLUIDA,
            self::CANCELADA,
        ],
        self::AGUARDANDO_APROVACAO => [
            self::CONCLUIDA,
            self::EM_ANDAMENTO,
            self::CANCELADA,
        ],
        self::CONCLUIDA  => [],
        self::CANCELADA  => [],
    ];

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
            self::ABERTA,
            self::EM_ANDAMENTO,
            self::AGUARDANDO_APROVACAO,
            self::CONCLUIDA,
            self::CANCELADA,
        ];
    }
}
