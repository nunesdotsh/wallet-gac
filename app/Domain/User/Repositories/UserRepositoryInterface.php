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

    /**
     * Persiste o estado atual da entidade User.
     *
     * Quando o usuário estiver com deactivatedAt definido, a implementação
     * deve registrar isso na coluna deleted_at (ou equivalente), garantindo
     * que consultas padrão excluam o registro — sem jamais apagá-lo.
     */
    public function save(User $user): void;

    public function existsByEmail(Email $email): bool;
}
