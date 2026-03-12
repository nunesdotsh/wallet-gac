<?php

declare(strict_types=1);

namespace App\Application\UseCases\Deposit;

/**
 * Dados de saída após uma operação de depósito.
 */
final readonly class DepositOutputDTO
{
    public function __construct(
        public string $transactionId,
        public string $walletId,
        public string $amount,
        public string $balanceBefore,
        public string $balanceAfter,
        public string $status,
    ) {}
}
