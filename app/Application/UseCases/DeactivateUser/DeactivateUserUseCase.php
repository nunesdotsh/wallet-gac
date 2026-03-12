<?php

declare(strict_types=1);

namespace App\Application\UseCases\DeactivateUser;

use App\Application\Contracts\UnitOfWorkInterface;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;

/**
 * Desativa a conta de um usuário e sua carteira associada via soft delete.
 *
 * Em sistemas financeiros, contas nunca são excluídas permanentemente.
 * O soft delete preserva o histórico completo de transações para fins
 * de auditoria, compliance e rastreabilidade.
 *
 * Após a desativação:
 * - O usuário não consegue mais autenticar-se
 * - A carteira e todas as transações permanecem íntegras no banco
 * - O registro pode ser reativado ou auditado por administradores
 */
final class DeactivateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly WalletRepositoryInterface $walletRepository,
        private readonly UnitOfWorkInterface $unitOfWork,
    ) {}

    /**
     * @param DeactivateUserInputDTO $input ID do usuário a desativar
     * @throws UserNotFoundException Quando o usuário não é encontrado
     */
    public function execute(DeactivateUserInputDTO $input): void
    {
        $userId = new UserId($input->userId);
        $user   = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new UserNotFoundException($input->userId);
        }

        $wallet = $this->walletRepository->findByUserId($userId);

        $user->deactivate();

        if ($wallet !== null) {
            $wallet->deactivate();
        }

        $this->unitOfWork->execute(function () use ($user, $wallet) {
            if ($wallet !== null) {
                $this->walletRepository->save($wallet);
            }

            $this->userRepository->save($user);
        });
    }
}
