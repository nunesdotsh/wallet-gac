<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Eloquent para a tabela transactions.
 *
 * @property string $id
 * @property string $wallet_id
 * @property string $type
 * @property string $amount
 * @property string $balance_before
 * @property string $balance_after
 * @property string $status
 * @property string|null $related_transaction_id
 * @property string|null $counterpart_wallet_id
 * @property string|null $description
 * @property string|null $reversed_at
 */
class TransactionModel extends Model
{
    use HasUuids;

    protected $table = 'transactions';

    protected $fillable = [
        'id',
        'wallet_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'status',
        'related_transaction_id',
        'counterpart_wallet_id',
        'description',
        'reversed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'reversed_at' => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(WalletModel::class, 'wallet_id');
    }

    public function relatedTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'related_transaction_id');
    }

    public function counterpartWallet(): BelongsTo
    {
        return $this->belongsTo(WalletModel::class, 'counterpart_wallet_id');
    }
}
