<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Transaction\ValueObjects\TransactionId;
use App\Domain\Transaction\ValueObjects\TransactionStatus;
use App\Domain\Transaction\ValueObjects\TransactionType;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletId;
use App\Infrastructure\Persistence\Eloquent\Models\TransactionModel;
use DateTimeImmutable;

/**
 * Implementação Eloquent do repositório de Transaction.
 */
final class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function findById(TransactionId $id): ?Transaction
    {
        $model = TransactionModel::find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    /**
     * @return Transaction[]
     */
    public function findByWalletId(WalletId $walletId): array
    {
        $models = TransactionModel::where('wallet_id', $walletId->value())
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn (TransactionModel $m) => $this->toDomain($m))->all();
    }

    public function save(Transaction $transaction): void
    {
        TransactionModel::updateOrCreate(
            ['id' => $transaction->id()->value()],
            [
                'wallet_id' => $transaction->walletId()->value(),
                'type' => $transaction->type()->value,
                'amount' => $transaction->amount()->toDecimal(),
                'balance_before' => $transaction->balanceBefore()->toDecimal(),
                'balance_after' => $transaction->balanceAfter()->toDecimal(),
                'status' => $transaction->status()->value,
                'related_transaction_id' => $transaction->relatedTransactionId()?->value(),
                'counterpart_wallet_id' => $transaction->counterpartWalletId()?->value(),
                'description' => $transaction->description(),
                'reversed_at' => $transaction->reversedAt()?->format('Y-m-d H:i:s'),
            ],
        );
    }

    private function toDomain(TransactionModel $model): Transaction
    {
        return new Transaction(
            id: new TransactionId($model->id),
            walletId: new WalletId($model->wallet_id),
            type: TransactionType::from($model->type),
            amount: Money::fromDecimal((string) $model->amount),
            balanceBefore: Money::fromDecimal((string) $model->balance_before),
            balanceAfter: Money::fromDecimal((string) $model->balance_after),
            status: TransactionStatus::from($model->status),
            relatedTransactionId: $model->related_transaction_id
                ? new TransactionId($model->related_transaction_id)
                : null,
            counterpartWalletId: $model->counterpart_wallet_id
                ? new WalletId($model->counterpart_wallet_id)
                : null,
            description: $model->description,
            reversedAt: $model->reversed_at
                ? new DateTimeImmutable($model->reversed_at->format('Y-m-d H:i:s'))
                : null,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
        );
    }
}
