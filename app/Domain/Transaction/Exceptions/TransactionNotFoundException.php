<?php

declare(strict_types=1);

namespace App\Domain\Transaction\Exceptions;

use App\Shared\Exceptions\DomainException;

/**
 * Lançada quando uma transação não é encontrada pelo identificador fornecido.
 */
final class TransactionNotFoundException extends DomainException
{
    public function __construct(string $identifier)
    {
        parent::__construct(
            message: "Transaction not found: {$identifier}",
            errorCode: 'TRANSACTION_NOT_FOUND',
            httpStatusCode: 404,
        );
    }
}
