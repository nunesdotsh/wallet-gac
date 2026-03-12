<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Exceptions;

use App\Shared\Exceptions\DomainException;

/**
 * Lançada quando uma carteira não é encontrada pelo identificador fornecido.
 */
final class WalletNotFoundException extends DomainException
{
    public function __construct(string $identifier)
    {
        parent::__construct(
            message: "Wallet not found: {$identifier}",
            errorCode: 'WALLET_NOT_FOUND',
            httpStatusCode: 404,
        );
    }
}
