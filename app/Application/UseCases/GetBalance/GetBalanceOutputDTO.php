<?php

declare(strict_types=1);

namespace App\Application\UseCases\GetBalance;

/**
 * Dados de saída para consulta de saldo.
 */
final readonly class GetBalanceOutputDTO
{
    public function __construct(
        public string $walletId,
        public string $userId,
        public string $balance,
    ) {}
}
