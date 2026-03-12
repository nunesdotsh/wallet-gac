<?php

declare(strict_types=1);

namespace App\Application\UseCases\ReverseTransaction;

/**
 * Dados de saída após o estorno da transação.
 */
final readonly class ReverseTransactionOutputDTO
{
    public function __construct(
        public string $reversalTransactionId,
        public string $originalTransactionId,
        public string $walletId,
        public string $amount,
        public string $balanceBefore,
        public string $balanceAfter,
        public string $status,
    ) {}
}
