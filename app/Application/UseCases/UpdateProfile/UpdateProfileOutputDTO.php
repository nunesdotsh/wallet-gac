<?php

declare(strict_types=1);

namespace App\Application\UseCases\UpdateProfile;

/**
 * Dados de saída após atualização de perfil do usuário.
 */
final readonly class UpdateProfileOutputDTO
{
    public function __construct(
        public string $userId,
        public string $name,
        public string $email,
        public bool $emailChanged,
    ) {}
}
