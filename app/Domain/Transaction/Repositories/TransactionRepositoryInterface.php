<?php

declare(strict_types=1);

namespace App\Domain\Transaction\Repositories;

use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\ValueObjects\TransactionId;
use App\Domain\Wallet\ValueObjects\WalletId;

/**
 * Contrato para operações de persistência de Transaction.
 */
interface TransactionRepositoryInterface
{
    public function findById(TransactionId $id): ?Transaction;

    /**
     * Retorna todas as transações de uma carteira específica, ordenadas por data de criação decrescente.
     *
     * @param WalletId $walletId ID da carteira
     * @return Transaction[]
     */
    public function findByWalletId(WalletId $walletId): array;

    public function save(Transaction $transaction): void;
}
