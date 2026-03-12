<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use App\Shared\Exceptions\DomainException;

/**
 * Lançada ao tentar registrar um e-mail que já está em uso.
 */
final class DuplicateEmailException extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(
            message: "A user with email '{$email}' already exists.",
            errorCode: 'DUPLICATE_EMAIL',
            httpStatusCode: 409,
        );
    }
}
