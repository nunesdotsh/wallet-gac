<?php

declare(strict_types=1);

namespace App\Domain\Transaction\Exceptions;

use App\Shared\Exceptions\DomainException;

/**
 * Lançada ao tentar estornar uma transação que já foi estornada.
 */
final class TransactionAlreadyReversedException extends DomainException
{
    public function __construct(string $transactionId)
    {
        parent::__construct(
            message: "Transaction '{$transactionId}' has already been reversed.",
            errorCode: 'TRANSACTION_ALREADY_REVERSED',
            httpStatusCode: 409,
        );
    }
}
