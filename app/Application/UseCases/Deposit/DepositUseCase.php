<?php

declare(strict_types=1);

namespace App\Application\UseCases\Deposit;

use App\Application\Contracts\UnitOfWorkInterface;
use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Exceptions\NegativeAmountException;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObjects\Money;

/**
 * Deposita dinheiro na carteira de um usuário.
 *
 * Trata depósitos incluindo o caso em que a carteira possui saldo negativo —
 * o valor do depósito é somado ao saldo atual, reduzindo a dívida.
 */
final class DepositUseCase
{
    public function __construct(
        private readonly WalletRepositoryInterface $walletRepository,
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly UnitOfWorkInterface $unitOfWork,
    ) {}

    /**
     * @param DepositInputDTO $input Dados do depósito
     * @return DepositOutputDTO Resultado do depósito com informações de saldo
     * @throws WalletNotFoundException Quando a carteira não é encontrada
     * @throws NegativeAmountException Quando o valor não é positivo
     */
    public function execute(DepositInputDTO $input): DepositOutputDTO
    {
        $amount = Money::fromDecimal($input->amount);

        if (!$amount->isPositive()) {
            throw new NegativeAmountException($input->amount);
        }

        $userId = new UserId($input->userId);

        return $this->unitOfWork->execute(function () use ($userId, $amount, $input) {
            $wallet = $this->walletRepository->findByUserIdForUpdate($userId);

            if ($wallet === null) {
                throw new WalletNotFoundException($userId->value());
            }

            $balanceBefore = $wallet->balance();
            $wallet->credit($amount);
            $balanceAfter = $wallet->balance();

            $transaction = Transaction::createDeposit(
                walletId: $wallet->id(),
                amount: $amount,
                balanceBefore: $balanceBefore,
                balanceAfter: $balanceAfter,
                description: $input->description,
            );

            $this->walletRepository->save($wallet);
            $this->transactionRepository->save($transaction);

            return new DepositOutputDTO(
                transactionId: $transaction->id()->value(),
                walletId: $wallet->id()->value(),
                amount: $amount->toDecimal(),
                balanceBefore: $balanceBefore->toDecimal(),
                balanceAfter: $balanceAfter->toDecimal(),
                status: $transaction->status()->value,
            );
        });
    }
}
