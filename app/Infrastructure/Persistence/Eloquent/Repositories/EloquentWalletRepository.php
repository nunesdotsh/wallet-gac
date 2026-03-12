<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Entities\Wallet;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletId;
use App\Infrastructure\Persistence\Eloquent\Models\WalletModel;

/**
 * Implementação Eloquent do repositório de Wallet.
 *
 * Suporta bloqueio pessimista via lockForUpdate() para
 * modificações concorrentes de saldo.
 */
final class EloquentWalletRepository implements WalletRepositoryInterface
{
    public function findById(WalletId $id): ?Wallet
    {
        $model = WalletModel::find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    public function findByUserId(UserId $userId): ?Wallet
    {
        $model = WalletModel::where('user_id', $userId->value())->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByIdForUpdate(WalletId $id): ?Wallet
    {
        $model = WalletModel::where('id', $id->value())
            ->lockForUpdate()
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByUserIdForUpdate(UserId $userId): ?Wallet
    {
        $model = WalletModel::where('user_id', $userId->value())
            ->lockForUpdate()
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function save(Wallet $wallet): void
    {
        WalletModel::updateOrCreate(
            ['id' => $wallet->id()->value()],
            [
                'user_id' => $wallet->userId()->value(),
                'balance' => $wallet->balance()->toDecimal(),
            ],
        );
    }

    private function toDomain(WalletModel $model): Wallet
    {
        return new Wallet(
            id: new WalletId($model->id),
            userId: new UserId($model->user_id),
            balance: Money::fromDecimal((string) $model->balance),
        );
    }
}
