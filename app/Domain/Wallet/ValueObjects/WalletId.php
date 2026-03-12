<?php

declare(strict_types=1);

namespace App\Domain\Wallet\ValueObjects;

use App\Shared\ValueObjects\Uuid;

/**
 * Identificador fortemente tipado para entidades Wallet.
 */
final class WalletId extends Uuid {}
