<?php

declare(strict_types=1);

namespace App\Shared\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid as RamseyUuid;

/**
 * Value object UUID imutável.
 *
 * Encapsula uma string UUID garantindo validade na construção.
 * Utilizado como base para value objects de ID específicos do domínio.
 */
class Uuid
{
    private readonly string $value;

    /**
     * @param string $value Uma string UUID v4 válida
     * @throws InvalidArgumentException Quando o formato do UUID é inválido
     */
    public function __construct(string $value)
    {
        if (!RamseyUuid::isValid($value)) {
            throw new InvalidArgumentException("Invalid UUID: {$value}");
        }

        $this->value = $value;
    }

    public static function generate(): static
    {
        return new static(RamseyUuid::uuid4()->toString());
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
