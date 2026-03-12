<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use App\Shared\Exceptions\DomainException;

/**
 * Lançada quando um endereço de e-mail possui formato inválido.
 */
final class InvalidEmailException extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(
            message: "The email address '{$email}' is not valid.",
            errorCode: 'INVALID_EMAIL',
            httpStatusCode: 422,
        );
    }
}
