<?php

declare(strict_types=1);

namespace App\Application\UseCases\UpdatePassword;

use App\Application\Contracts\UnitOfWorkInterface;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\HashedPassword;
use App\Domain\User\ValueObjects\UserId;

/**
 * Atualiza a senha de um usuário existente.
 *
 * A verificação da senha atual é responsabilidade da camada de apresentação
 * (Fortify / form request). Este caso de uso apenas persiste a nova senha.
 */
final class UpdatePasswordUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UnitOfWorkInterface $unitOfWork,
    ) {}

    /**
     * @param UpdatePasswordInputDTO $input Dados com userId e nova senha em texto plano
     * @throws UserNotFoundException Quando o usuário não é encontrado
     */
    public function execute(UpdatePasswordInputDTO $input): void
    {
        $userId = new UserId($input->userId);
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new UserNotFoundException($input->userId);
        }

        $this->unitOfWork->execute(function () use ($user, $input) {
            $user->changePassword(HashedPassword::fromPlain($input->newPassword));
            $this->userRepository->save($user);
        });
    }
}
