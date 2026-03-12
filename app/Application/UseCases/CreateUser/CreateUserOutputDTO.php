<?php

declare(strict_types=1);

namespace App\Application\UseCases\CreateUser;

/**
 * Dados de saída após a criação do usuário.
 */
final readonly class CreateUserOutputDTO
{
    public function __construct(
        public string $userId,
        public string $walletId,
        public string $name,
        public string $email,
    ) {}
}
