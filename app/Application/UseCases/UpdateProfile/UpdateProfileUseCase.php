<?php

declare(strict_types=1);

namespace App\Application\UseCases\UpdateProfile;

use App\Application\Contracts\UnitOfWorkInterface;
use App\Domain\User\Exceptions\DuplicateEmailException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;

/**
 * Atualiza o nome e/ou e-mail de um usuário existente.
 *
 * Valida unicidade do e-mail caso seja alterado. Informa ao chamador
 * se o e-mail foi modificado para que a camada de apresentação possa
 * invalidar a verificação de e-mail quando necessário.
 */
final class UpdateProfileUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UnitOfWorkInterface $unitOfWork,
    ) {}

    /**
     * @param UpdateProfileInputDTO $input Dados do perfil a atualizar
     * @return UpdateProfileOutputDTO Perfil atualizado com indicação de mudança de e-mail
     * @throws UserNotFoundException   Quando o usuário não é encontrado
     * @throws DuplicateEmailException Quando o novo e-mail já está em uso por outro usuário
     */
    public function execute(UpdateProfileInputDTO $input): UpdateProfileOutputDTO
    {
        $userId = new UserId($input->userId);
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new UserNotFoundException($input->userId);
        }

        $newEmail = new Email($input->email);
        $emailChanged = $user->email()->value() !== $newEmail->value();

        if ($emailChanged && $this->userRepository->existsByEmail($newEmail)) {
            throw new DuplicateEmailException($input->email);
        }

        $this->unitOfWork->execute(function () use ($user, $input, $newEmail, $emailChanged) {
            $user->changeName($input->name);

            if ($emailChanged) {
                $user->changeEmail($newEmail);
            }

            $this->userRepository->save($user);
        });

        return new UpdateProfileOutputDTO(
            userId: $user->id()->value(),
            name: $user->name(),
            email: $user->email()->value(),
            emailChanged: $emailChanged,
        );
    }
}
