<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Shared\ValueObjects\Uuid;

/**
 * Identificador fortemente tipado para entidades User.
 */
final class UserId extends Uuid {}
