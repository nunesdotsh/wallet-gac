<?php

declare(strict_types=1);

namespace App\Domain\User\Entities;

use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\HashedPassword;
use App\Domain\User\ValueObjects\UserId;

/**
 * Entidade de domínio do usuário.
 *
 * Representa um usuário do sistema que pode possuir uma carteira e realizar
 * operações financeiras. Trata-se de um objeto de domínio puro,
 * sem dependências de infraestrutura.
 */
final class User
{
    /**
     * @param UserId         $id       Identificador único
     * @param string         $name     Nome completo do usuário
     * @param Email          $email    Endereço de e-mail do usuário
     * @param HashedPassword $password Senha encapsulada no value object
     */
    public function __construct(
        private readonly UserId $id,
        private string $name,
        private readonly Email $email,
        private readonly HashedPassword $password,
    ) {}

    /**
     * Método de fábrica para criar um novo User com UUID gerado.
     *
     * @param string         $name     Nome completo do usuário
     * @param Email          $email    Endereço de e-mail do usuário
     * @param HashedPassword $password Senha encapsulada
     * @return self
     */
    public static function create(string $name, Email $email, HashedPassword $password): self
    {
        return new self(
            id: UserId::generate(),
            name: $name,
            email: $email,
            password: $password,
        );
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function password(): HashedPassword
    {
        return $this->password;
    }

    public function changeName(string $name): void
    {
        $this->name = $name;
    }
}
