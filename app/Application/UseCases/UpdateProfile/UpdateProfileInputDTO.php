<?php

declare(strict_types=1);

namespace App\Application\UseCases\UpdateProfile;

/**
 * Dados de entrada para atualização de perfil do usuário.
 */
final readonly class UpdateProfileInputDTO
{
    public function __construct(
        public string $userId,
        public string $name,
        public string $email,
    ) {}
}
