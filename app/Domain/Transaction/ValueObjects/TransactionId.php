<?php

declare(strict_types=1);

namespace App\Domain\Transaction\ValueObjects;

use App\Shared\ValueObjects\Uuid;

/**
 * Identificador fortemente tipado para entidades Transaction.
 */
final class TransactionId extends Uuid {}
