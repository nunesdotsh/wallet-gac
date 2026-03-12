<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Entities;

use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletId;

/**
 * Entidade de domínio da carteira.
 *
 * Representa a carteira financeira de um usuário com gerenciamento de saldo.
 * Contém a lógica de negócio para créditos, débitos e validação de saldo.
 */
final class Wallet
{
    /**
     * @param WalletId $id Identificador único
     * @param UserId $userId ID do usuário proprietário
     * @param Money $balance Saldo atual
     */
    public function __construct(
        private readonly WalletId $id,
        private readonly UserId $userId,
        private Money $balance,
    ) {}

    /**
     * Método de fábrica para criar uma nova Wallet com saldo zero.
     *
     * @param UserId $userId ID do usuário proprietário
     * @return self
     */
    public static function create(UserId $userId): self
    {
        return new self(
            id: WalletId::generate(),
            userId: $userId,
            balance: Money::zero(),
        );
    }

    public function id(): WalletId
    {
        return $this->id;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function balance(): Money
    {
        return $this->balance;
    }

    /**
     * Adiciona dinheiro ao saldo da carteira.
     *
     * Funciona corretamente mesmo com saldos negativos — o depósito
     * é somado ao valor atual, reduzindo a dívida.
     *
     * @param Money $amount Valor a creditar (deve ser positivo)
     */
    public function credit(Money $amount): void
    {
        $this->balance = $this->balance->add($amount);
    }

    /**
     * Remove dinheiro do saldo da carteira.
     *
     * @param Money $amount Valor a debitar (deve ser positivo)
     * @throws InsufficientBalanceException Quando o saldo é insuficiente
     */
    public function debit(Money $amount): void
    {
        if (!$this->hasEnoughBalance($amount)) {
            throw new InsufficientBalanceException(
                $this->balance->toDecimal(),
                $amount->toDecimal(),
            );
        }

        $this->balance = $this->balance->subtract($amount);
    }

    /**
     * Verifica se a carteira possui saldo suficiente para o valor informado.
     *
     * @param Money $amount Valor a ser verificado
     * @return bool
     */
    public function hasEnoughBalance(Money $amount): bool
    {
        return $this->balance->isGreaterThanOrEqual($amount);
    }
}
