<?php

declare(strict_types=1);

namespace App\Application\UseCases\DeactivateUser;

/**
 * Dados de entrada para desativação de conta de usuário.
 */
final readonly class DeactivateUserInputDTO
{
    public function __construct(
        public string $userId,
    ) {}
}
