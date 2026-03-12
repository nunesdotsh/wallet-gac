<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\HashedPassword;
use App\Domain\User\ValueObjects\UserId;
use App\Models\User as UserModel;
use DateTimeImmutable;

/**
 * Implementação Eloquent do repositório de User.
 *
 * Realiza o mapeamento entre a entidade User do domínio e o model Eloquent User.
 */
final class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(UserId $id): ?User
    {
        $model = UserModel::find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    public function findByEmail(Email $email): ?User
    {
        $model = UserModel::where('email', $email->value())->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function save(User $user): void
    {
        $model = UserModel::withoutGlobalScopes()->firstOrNew(
            ['id' => $user->id()->value()]
        );

        $model->name       = $user->name();
        $model->email      = $user->email()->value();
        $model->password   = $user->password()->value();
        $model->deleted_at = $user->deactivatedAt();

        $model->save();
    }

    public function existsByEmail(Email $email): bool
    {
        return UserModel::where('email', $email->value())->exists();
    }

    private function toDomain(UserModel $model): User
    {
        return new User(
            id: new UserId($model->id),
            name: $model->name,
            email: new Email($model->email),
            password: HashedPassword::fromHash($model->password),
            deactivatedAt: $model->deleted_at
                ? new DateTimeImmutable($model->deleted_at)
                : null,
        );
    }
}
