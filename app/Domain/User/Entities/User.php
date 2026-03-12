<?php

declare(strict_types=1);

namespace App\Domain\User\Entities;

use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\HashedPassword;
use App\Domain\User\ValueObjects\UserId;
use DateTimeImmutable;

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
     * @param UserId                  $id             Identificador único
     * @param string                  $name           Nome completo do usuário
     * @param Email                   $email          Endereço de e-mail do usuário
     * @param HashedPassword          $password       Senha encapsulada no value object
     * @param DateTimeImmutable|null  $deactivatedAt  Momento da desativação, null se ativo
     */
    public function __construct(
        private readonly UserId $id,
        private string $name,
        private Email $email,
        private HashedPassword $password,
        private ?DateTimeImmutable $deactivatedAt = null,
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

    public function changeEmail(Email $email): void
    {
        $this->email = $email;
    }

    public function changePassword(HashedPassword $password): void
    {
        $this->password = $password;
    }

    /**
     * Desativa a conta do usuário.
     *
     * Em sistemas financeiros, contas não são removidas — são desativadas.
     * O histórico de transações e a carteira são preservados para auditoria.
     */
    public function deactivate(): void
    {
        $this->deactivatedAt = new DateTimeImmutable();
    }

    public function isActive(): bool
    {
        return $this->deactivatedAt === null;
    }

    public function deactivatedAt(): ?DateTimeImmutable
    {
        return $this->deactivatedAt;
    }
}
