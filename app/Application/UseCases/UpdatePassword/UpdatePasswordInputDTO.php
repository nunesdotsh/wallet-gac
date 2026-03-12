<?php

declare(strict_types=1);

namespace App\Application\UseCases\UpdatePassword;

/**
 * Dados de entrada para atualização de senha.
 */
final readonly class UpdatePasswordInputDTO
{
    public function __construct(
        public string $userId,
        public string $newPassword,
    ) {}
}
