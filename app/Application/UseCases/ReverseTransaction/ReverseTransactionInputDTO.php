<?php

declare(strict_types=1);

namespace App\Application\UseCases\ReverseTransaction;

/**
 * Dados de entrada para estorno de transação.
 */
final readonly class ReverseTransactionInputDTO
{
    public function __construct(
        public string $transactionId,
        public ?string $reason = null,
    ) {}
}
