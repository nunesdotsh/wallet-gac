<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

/**
 * Model Eloquent para a tabela wallets.
 *
 * @property string $id
 * @property string $user_id
 * @property string $balance
 */
class WalletModel extends Model
{
    use HasUuids;

    protected $table = 'wallets';

    protected $fillable = [
        'id',
        'user_id',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(TransactionModel::class, 'wallet_id');
    }
}
