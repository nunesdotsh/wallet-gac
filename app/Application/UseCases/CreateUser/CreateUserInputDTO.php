<?php

declare(strict_types=1);

namespace App\Application\UseCases\CreateUser;

/**
 * Dados de entrada para criação de usuário.
 */
final readonly class CreateUserInputDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}
}
