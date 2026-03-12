<?php

declare(strict_types=1);

namespace App\Application\UseCases\CreateUser;

use App\Application\Contracts\UnitOfWorkInterface;
use App\Domain\User\Entities\User;
use App\Domain\User\Exceptions\DuplicateEmailException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\HashedPassword;
use App\Domain\Wallet\Entities\Wallet;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;

/**
 * Cria um novo usuário e sua carteira associada.
 *
 * Este caso de uso trata o registro de usuários:
 * 1. Validando a unicidade do e-mail
 * 2. Criando a entidade User
 * 3. Criando a entidade Wallet com saldo zero
 * 4. Persistindo ambas em uma transação atômica
 */
final class CreateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly WalletRepositoryInterface $walletRepository,
        private readonly UnitOfWorkInterface $unitOfWork,
    ) {}

    /**
     * @param CreateUserInputDTO $input Dados de registro do usuário
     * @return CreateUserOutputDTO Informações do usuário e carteira criados
     * @throws DuplicateEmailException Quando o e-mail já está em uso
     */
    public function execute(CreateUserInputDTO $input): CreateUserOutputDTO
    {
        $email = new Email($input->email);

        if ($this->userRepository->existsByEmail($email)) {
            throw new DuplicateEmailException($input->email);
        }

        return $this->unitOfWork->execute(function () use ($input, $email) {
            $user = User::create($input->name, $email, HashedPassword::fromPlain($input->password));
            $wallet = Wallet::create($user->id());

            $this->userRepository->save($user);
            $this->walletRepository->save($wallet);

            return new CreateUserOutputDTO(
                userId: $user->id()->value(),
                walletId: $wallet->id()->value(),
                name: $user->name(),
                email: $user->email()->value(),
            );
        });
    }
}
