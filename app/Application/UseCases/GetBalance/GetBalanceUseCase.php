<?php

declare(strict_types=1);

namespace App\Application\UseCases\GetBalance;

use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;

/**
 * Recupera o saldo atual da carteira de um usuário.
 */
final class GetBalanceUseCase
{
    public function __construct(
        private readonly WalletRepositoryInterface $walletRepository,
    ) {}

    /**
     * @param string $userId ID do usuário
     * @return GetBalanceOutputDTO Informações de saldo
     * @throws WalletNotFoundException Quando a carteira não é encontrada
     */
    public function execute(string $userId): GetBalanceOutputDTO
    {
        $id = new UserId($userId);
        $wallet = $this->walletRepository->findByUserId($id);

        if ($wallet === null) {
            throw new WalletNotFoundException($userId);
        }

        return new GetBalanceOutputDTO(
            walletId: $wallet->id()->value(),
            userId: $wallet->userId()->value(),
            balance: $wallet->balance()->toDecimal(),
        );
    }
}
