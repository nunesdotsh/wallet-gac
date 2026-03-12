<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\User\Exceptions\InvalidEmailException;

/**
 * Value object de e-mail imutável com validação de formato.
 *
 * Valida o formato do e-mail na construção, garantindo
 * que e-mails inválidos nunca existam no domínio.
 */
final class Email
{
    private readonly string $value;

    /**
     * @param string $value Endereço de e-mail
     * @throws InvalidEmailException Quando o formato do e-mail é inválido
     */
    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (!filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException($value);
        }

        $this->value = $normalized;
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
