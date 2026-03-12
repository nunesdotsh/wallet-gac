<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;

/**
 * Contrato para operações de persistência de User.
 *
 * Implementações podem utilizar Eloquent, Doctrine ou qualquer outro
 * mecanismo de persistência sem afetar a camada de domínio.
 */
interface UserRepositoryInterface
{
    public function findById(UserId $id): ?User;

    public function findByEmail(Email $email): ?User;

    public function save(User $user): void;

    public function existsByEmail(Email $email): bool;
}
