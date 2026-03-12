<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use App\Shared\Exceptions\DomainException;

/**
 * Lançada quando um usuário não é encontrado pelo identificador fornecido.
 */
final class UserNotFoundException extends DomainException
{
    public function __construct(string $identifier)
    {
        parent::__construct(
            message: "User not found: {$identifier}",
            errorCode: 'USER_NOT_FOUND',
            httpStatusCode: 404,
        );
    }
}
