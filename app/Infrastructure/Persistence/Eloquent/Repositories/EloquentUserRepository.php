<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\HashedPassword;
use App\Domain\User\ValueObjects\UserId;
use App\Models\User as UserModel;

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
        UserModel::updateOrCreate(
            ['id' => $user->id()->value()],
            [
                'name'     => $user->name(),
                'email'    => $user->email()->value(),
                'password' => $user->password()->value(),
            ],
        );
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
        );
    }
}
