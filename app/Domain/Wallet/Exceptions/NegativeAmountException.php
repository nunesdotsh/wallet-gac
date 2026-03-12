<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

use App\Shared\Exceptions\DomainException;

/**
 * Lançada quando uma operação financeira é tentada com valor negativo ou zero.
 */
final class NegativeAmountException extends DomainException
{
    public function __construct(string $amount)
    {
        parent::__construct(
            message: "The amount must be greater than zero. Given: {$amount}",
            errorCode: 'NEGATIVE_AMOUNT',
            httpStatusCode: 422,
        );
    }
}
