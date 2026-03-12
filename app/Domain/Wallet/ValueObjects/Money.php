<?php

declare(strict_types=1);

namespace App\Domain\Wallet\ValueObjects;

use App\Domain\Wallet\Exceptions\NegativeAmountException;

/**
 * Value object monetário imutável com precisão baseada em centavos.
 *
 * Armazena valores como inteiros (centavos) para evitar problemas de precisão
 * com ponto flutuante. Todas as operações aritméticas retornam novas instâncias de Money.
 *
 * Exemplos:
 *   new Money(1050) representa R$ 10,50
 *   Money::fromDecimal('10.50') também representa R$ 10,50
 */
final class Money
{
    /**
     * @param int $cents Valor em centavos (ex: 1050 = R$ 10,50)
     */
    public function __construct(
        private readonly int $cents,
    ) {}

    /**
     * Cria uma instância de Money a partir de uma representação decimal em string.
     *
     * @param string $amount Valor decimal (ex: "10.50")
     * @return self
     */
    public static function fromDecimal(string $amount): self
    {
        $cents = (int) round((float) $amount * 100);

        return new self($cents);
    }

    /**
     * Cria uma instância de Money representando valor zero.
     *
     * @return self
     */
    public static function zero(): self
    {
        return new self(0);
    }

    public function cents(): int
    {
        return $this->cents;
    }

    /**
     * Retorna a representação decimal em string com 2 casas decimais.
     *
     * @return string Valor formatado (ex: "10.50")
     */
    public function toDecimal(): string
    {
        return number_format($this->cents / 100, 2, '.', '');
    }

    /**
     * @throws NegativeAmountException Quando o valor resultante seria negativo e $allowNegative é false
     */
    public function add(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    /**
     * @return self Nova instância de Money com o valor subtraído
     */
    public function subtract(self $other): self
    {
        return new self($this->cents - $other->cents);
    }

    public function isPositive(): bool
    {
        return $this->cents > 0;
    }

    public function isNegative(): bool
    {
        return $this->cents < 0;
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }

    public function isGreaterThanOrEqual(self $other): bool
    {
        return $this->cents >= $other->cents;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->cents > $other->cents;
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents;
    }

    public function __toString(): string
    {
        return $this->toDecimal();
    }
}
