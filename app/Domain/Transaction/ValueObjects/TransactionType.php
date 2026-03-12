<?php

declare(strict_types=1);

namespace App\Domain\Transaction\ValueObjects;

/**
 * Define o tipo de uma transação financeira.
 *
 * DEPOSIT: Dinheiro adicionado a uma carteira de fonte externa.
 * TRANSFER_IN: Dinheiro recebido de outra carteira.
 * TRANSFER_OUT: Dinheiro enviado para outra carteira.
 */
enum TransactionType: string
{
    case DEPOSIT = 'deposit';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';

    /**
     * Retorna true se este tipo representa dinheiro entrando em uma carteira.
     */
    public function isCredit(): bool
    {
        return match ($this) {
            self::DEPOSIT, self::TRANSFER_IN => true,
            self::TRANSFER_OUT => false,
        };
    }

    /**
     * Retorna true se este tipo representa dinheiro saindo de uma carteira.
     */
    public function isDebit(): bool
    {
        return !$this->isCredit();
    }
}
