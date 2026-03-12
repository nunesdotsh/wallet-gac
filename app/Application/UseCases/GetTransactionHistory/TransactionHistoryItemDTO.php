<?php

declare(strict_types=1);

namespace App\Application\UseCases\GetTransactionHistory;

/**
 * Dados de saída para uma transação individual no histórico.
 */
final readonly class TransactionHistoryItemDTO
{
    public function __construct(
        public string $transactionId,
        public string $type,
        public string $amount,
        public string $balanceBefore,
        public string $balanceAfter,
        public string $status,
        public ?string $counterpartWalletId,
        public ?string $counterpartName,
        public ?string $counterpartEmail,
        public ?string $description,
        public ?string $reversedAt,
        public string $createdAt,
    ) {}
}
