<?php

declare(strict_types=1);

namespace App\Application\UseCases\Deposit;

/**
 * Dados de entrada para operação de depósito.
 */
final readonly class DepositInputDTO
{
    public function __construct(
        public string $userId,
        public string $amount,
        public ?string $description = null,
    ) {}
}
