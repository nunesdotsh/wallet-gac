<?php

declare(strict_types=1);

namespace App\Application\UseCases\GetTransactionHistory;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObjects\WalletId;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;

/**
 * Recupera o histórico de transações da carteira de um usuário.
 */
final class GetTransactionHistoryUseCase
{
    public function __construct(
        private readonly WalletRepositoryInterface $walletRepository,
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @param string $userId ID do usuário
     * @return TransactionHistoryItemDTO[] Lista de transações
     * @throws WalletNotFoundException Quando a carteira não é encontrada
     */
    public function execute(string $userId): array
    {
        $id = new UserId($userId);
        $wallet = $this->walletRepository->findByUserId($id);

        if ($wallet === null) {
            throw new WalletNotFoundException($userId);
        }

        $transactions = $this->transactionRepository->findByWalletId($wallet->id());

        $counterpartCache = [];

        return array_map(function ($tx) use (&$counterpartCache) {
            $counterpartName  = null;
            $counterpartEmail = null;

            $walletIdValue = $tx->counterpartWalletId()?->value();

            if ($walletIdValue !== null) {
                if (!array_key_exists($walletIdValue, $counterpartCache)) {
                    $counterpartWallet = $this->walletRepository->findById(new WalletId($walletIdValue));
                    if ($counterpartWallet !== null) {
                        $counterpartUser = $this->userRepository->findById($counterpartWallet->userId());
                        $counterpartCache[$walletIdValue] = $counterpartUser !== null
                            ? ['name' => $counterpartUser->name(), 'email' => $counterpartUser->email()->value()]
                            : null;
                    } else {
                        $counterpartCache[$walletIdValue] = null;
                    }
                }

                $counterpartName  = $counterpartCache[$walletIdValue]['name'] ?? null;
                $counterpartEmail = $counterpartCache[$walletIdValue]['email'] ?? null;
            }

            return new TransactionHistoryItemDTO(
                transactionId: $tx->id()->value(),
                type: $tx->type()->value,
                amount: $tx->amount()->toDecimal(),
                balanceBefore: $tx->balanceBefore()->toDecimal(),
                balanceAfter: $tx->balanceAfter()->toDecimal(),
                status: $tx->status()->value,
                counterpartWalletId: $walletIdValue,
                counterpartName: $counterpartName,
                counterpartEmail: $counterpartEmail,
                description: $tx->description(),
                reversedAt: $tx->reversedAt()?->format('Y-m-d H:i:s'),
                createdAt: $tx->createdAt()->format('Y-m-d H:i:s'),
            );
        }, $transactions);
    }
}
