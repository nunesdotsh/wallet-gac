<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

/**
 * Value object que representa uma senha hasheada com segurança.
 *
 * Centraliza o algoritmo de hashing no domínio, garantindo que nenhuma
 * camada externa (Presentation, Infrastructure) saiba como a senha é
 * armazenada. Para trocar o algoritmo, basta alterar este arquivo.
 */
final class HashedPassword
{
    private function __construct(
        private readonly string $hash,
    ) {}

    /**
     * Cria um HashedPassword a partir de uma senha em texto plano.
     * Utiliza PASSWORD_BCRYPT nativo do PHP, sem dependência de framework.
     *
     * @param string $plain Senha em texto plano
     * @return self
     */
    public static function fromPlain(string $plain): self
    {
        return new self(password_hash($plain, PASSWORD_BCRYPT));
    }

    /**
     * Reconstrói o value object a partir de um hash já armazenado (ex: banco de dados).
     *
     * @param string $hash Hash bcrypt já processado
     * @return self
     */
    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    /**
     * Verifica se uma senha em texto plano corresponde a este hash.
     * Utiliza password_verify nativo do PHP.
     *
     * @param string $plain Senha em texto plano para verificar
     * @return bool
     */
    public function verify(string $plain): bool
    {
        return password_verify($plain, $this->hash);
    }

    /**
     * Retorna o hash armazenável (para persistência).
     *
     * @return string
     */
    public function value(): string
    {
        return $this->hash;
    }

    public function equals(self $other): bool
    {
        return $this->hash === $other->hash;
    }
}
