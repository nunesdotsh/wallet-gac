<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

use App\Shared\Exceptions\DomainException;

/**
 * Lançada quando uma transferência é tentada com saldo insuficiente.
 */
final class InsufficientBalanceException extends DomainException
{
    /**
     * @param string $currentBalance Saldo atual formatado
     * @param string $requestedAmount Valor solicitado formatado
     */
    public function __construct(string $currentBalance, string $requestedAmount)
    {
        parent::__construct(
            message: "Insufficient balance. Current: {$currentBalance}, Requested: {$requestedAmount}",
            errorCode: 'INSUFFICIENT_BALANCE',
            httpStatusCode: 422,
        );
    }
}
