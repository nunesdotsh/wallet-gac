<?php

declare(strict_types=1);

namespace App\Application\UseCases\GetTransactionHistory;

use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;

/**
 * Recupera o histórico de transações da carteira de um usuário.
 */
final class GetTransactionHistoryUseCase
{
    public function __construct(
        private readonly WalletRepositoryInterface $walletRepository,
        private readonly TransactionRepositoryInterface $transactionRepository,
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

        return array_map(
            fn ($tx) => new TransactionHistoryItemDTO(
                transactionId: $tx->id()->value(),
                type: $tx->type()->value,
                amount: $tx->amount()->toDecimal(),
                balanceBefore: $tx->balanceBefore()->toDecimal(),
                balanceAfter: $tx->balanceAfter()->toDecimal(),
                status: $tx->status()->value,
                counterpartWalletId: $tx->counterpartWalletId()?->value(),
                description: $tx->description(),
                reversedAt: $tx->reversedAt()?->format('Y-m-d H:i:s'),
                createdAt: $tx->createdAt()->format('Y-m-d H:i:s'),
            ),
            $transactions,
        );
    }
}
