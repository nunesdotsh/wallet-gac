<?php

declare(strict_types=1);

namespace App\Domain\Transaction\Entities;

use App\Domain\Transaction\Exceptions\TransactionAlreadyReversedException;
use App\Domain\Transaction\ValueObjects\TransactionId;
use App\Domain\Transaction\ValueObjects\TransactionStatus;
use App\Domain\Transaction\ValueObjects\TransactionType;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletId;
use DateTimeImmutable;

/**
 * Entidade de domínio da transação.
 *
 * Representa uma operação financeira (depósito, transferência de entrada/saída) com
 * trilha de auditoria completa incluindo snapshots de saldo antes e depois.
 */
final class Transaction
{
    /**
     * @param TransactionId $id Identificador único
     * @param WalletId $walletId Carteira à qual esta transação pertence
     * @param TransactionType $type Tipo da transação
     * @param Money $amount Valor da transação (sempre positivo)
     * @param Money $balanceBefore Saldo da carteira antes desta transação
     * @param Money $balanceAfter Saldo da carteira após esta transação
     * @param TransactionStatus $status Status atual
     * @param TransactionId|null $relatedTransactionId Transação relacionada (para estornos)
     * @param WalletId|null $counterpartWalletId Carteira contraparte (para transferências)
     * @param string|null $description Descrição opcional
     * @param DateTimeImmutable|null $reversedAt Quando a transação foi estornada
     * @param DateTimeImmutable $createdAt Timestamp de criação
     */
    public function __construct(
        private readonly TransactionId $id,
        private readonly WalletId $walletId,
        private readonly TransactionType $type,
        private readonly Money $amount,
        private readonly Money $balanceBefore,
        private readonly Money $balanceAfter,
        private TransactionStatus $status,
        private readonly ?TransactionId $relatedTransactionId = null,
        private readonly ?WalletId $counterpartWalletId = null,
        private readonly ?string $description = null,
        private ?DateTimeImmutable $reversedAt = null,
        private readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
    ) {}

    /**
     * Método de fábrica para criar uma transação de depósito.
     *
     * @param WalletId $walletId Carteira de destino
     * @param Money $amount Valor do depósito
     * @param Money $balanceBefore Saldo antes do depósito
     * @param Money $balanceAfter Saldo após o depósito
     * @param string|null $description Descrição opcional
     * @return self
     */
    public static function createDeposit(
        WalletId $walletId,
        Money $amount,
        Money $balanceBefore,
        Money $balanceAfter,
        ?string $description = null,
    ): self {
        return new self(
            id: TransactionId::generate(),
            walletId: $walletId,
            type: TransactionType::DEPOSIT,
            amount: $amount,
            balanceBefore: $balanceBefore,
            balanceAfter: $balanceAfter,
            status: TransactionStatus::COMPLETED,
            description: $description,
        );
    }

    /**
     * Método de fábrica para criar uma transação de transferência de saída (lado do remetente).
     *
     * @param WalletId $walletId Carteira do remetente
     * @param WalletId $counterpartWalletId Carteira do destinatário
     * @param Money $amount Valor da transferência
     * @param Money $balanceBefore Saldo do remetente antes da transferência
     * @param Money $balanceAfter Saldo do remetente após a transferência
     * @param string|null $description Descrição opcional
     * @return self
     */
    public static function createTransferOut(
        WalletId $walletId,
        WalletId $counterpartWalletId,
        Money $amount,
        Money $balanceBefore,
        Money $balanceAfter,
        ?string $description = null,
    ): self {
        return new self(
            id: TransactionId::generate(),
            walletId: $walletId,
            type: TransactionType::TRANSFER_OUT,
            amount: $amount,
            balanceBefore: $balanceBefore,
            balanceAfter: $balanceAfter,
            status: TransactionStatus::COMPLETED,
            counterpartWalletId: $counterpartWalletId,
            description: $description,
        );
    }

    /**
     * Método de fábrica para criar uma transação de transferência de entrada (lado do destinatário).
     *
     * @param WalletId $walletId Carteira do destinatário
     * @param WalletId $counterpartWalletId Carteira do remetente
     * @param Money $amount Valor da transferência
     * @param Money $balanceBefore Saldo do destinatário antes da transferência
     * @param Money $balanceAfter Saldo do destinatário após a transferência
     * @param TransactionId $relatedTransactionId ID da transação de transferência de saída
     * @param string|null $description Descrição opcional
     * @return self
     */
    public static function createTransferIn(
        WalletId $walletId,
        WalletId $counterpartWalletId,
        Money $amount,
        Money $balanceBefore,
        Money $balanceAfter,
        TransactionId $relatedTransactionId,
        ?string $description = null,
    ): self {
        return new self(
            id: TransactionId::generate(),
            walletId: $walletId,
            type: TransactionType::TRANSFER_IN,
            amount: $amount,
            balanceBefore: $balanceBefore,
            balanceAfter: $balanceAfter,
            status: TransactionStatus::COMPLETED,
            relatedTransactionId: $relatedTransactionId,
            counterpartWalletId: $counterpartWalletId,
            description: $description,
        );
    }

    /**
     * Marca esta transação como estornada.
     *
     * @throws TransactionAlreadyReversedException Quando já foi estornada
     */
    public function markAsReversed(): void
    {
        if ($this->status->isReversed()) {
            throw new TransactionAlreadyReversedException($this->id->value());
        }

        $this->status = TransactionStatus::REVERSED;
        $this->reversedAt = new DateTimeImmutable();
    }

    public function id(): TransactionId
    {
        return $this->id;
    }

    public function walletId(): WalletId
    {
        return $this->walletId;
    }

    public function type(): TransactionType
    {
        return $this->type;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function balanceBefore(): Money
    {
        return $this->balanceBefore;
    }

    public function balanceAfter(): Money
    {
        return $this->balanceAfter;
    }

    public function status(): TransactionStatus
    {
        return $this->status;
    }

    public function relatedTransactionId(): ?TransactionId
    {
        return $this->relatedTransactionId;
    }

    public function counterpartWalletId(): ?WalletId
    {
        return $this->counterpartWalletId;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function reversedAt(): ?DateTimeImmutable
    {
        return $this->reversedAt;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function canBeReversed(): bool
    {
        return $this->status->canBeReversed();
    }
}
