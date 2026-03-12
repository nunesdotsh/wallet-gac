<?php

declare(strict_types=1);

namespace App\Domain\Transaction\Exceptions;

use App\Shared\Exceptions\DomainException;

/**
 * Lançada quando uma transação viola regras de negócio.
 *
 * Exemplos: transferência para si mesmo, tipo inválido para a operação, etc.
 */
final class InvalidTransactionException extends DomainException
{
    public function __construct(string $reason)
    {
        parent::__construct(
            message: "Invalid transaction: {$reason}",
            errorCode: 'INVALID_TRANSACTION',
            httpStatusCode: 422,
        );
    }
}
