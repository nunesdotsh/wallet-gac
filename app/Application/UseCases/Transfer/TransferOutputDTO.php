<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

/**
 * Dados de saída após uma operação de transferência.
 */
final readonly class TransferOutputDTO
{
    public function __construct(
        public string $senderTransactionId,
        public string $receiverTransactionId,
        public string $senderWalletId,
        public string $receiverWalletId,
        public string $amount,
        public string $senderBalanceBefore,
        public string $senderBalanceAfter,
        public string $receiverBalanceBefore,
        public string $receiverBalanceAfter,
        public string $status,
    ) {}
}
