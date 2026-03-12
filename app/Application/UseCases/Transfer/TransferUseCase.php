<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\Contracts\UnitOfWorkInterface;
use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Exceptions\InvalidTransactionException;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Exceptions\NegativeAmountException;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObjects\Money;

/**
 * Transfere dinheiro entre duas carteiras.
 *
 * Cria duas transações vinculadas (transfer_out para o remetente,
 * transfer_in para o destinatário) dentro de uma transação atômica de banco de dados.
 * Valida a suficiência do saldo antes de prosseguir.
 */
final class TransferUseCase
{
    public function __construct(
        private readonly WalletRepositoryInterface $walletRepository,
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly UnitOfWorkInterface $unitOfWork,
    ) {}

    /**
     * @param TransferInputDTO $input Dados da transferência
     * @return TransferOutputDTO Resultado da transferência com informações de saldo
     * @throws InvalidTransactionException Quando o remetente é igual ao destinatário
     * @throws NegativeAmountException Quando o valor não é positivo
     * @throws WalletNotFoundException Quando uma carteira não é encontrada
     */
    public function execute(TransferInputDTO $input): TransferOutputDTO
    {
        $amount = Money::fromDecimal($input->amount);

        if (!$amount->isPositive()) {
            throw new NegativeAmountException($input->amount);
        }

        $senderUserId = new UserId($input->senderUserId);
        $receiverUserId = new UserId($input->receiverUserId);

        if ($senderUserId->equals($receiverUserId)) {
            throw new InvalidTransactionException('Cannot transfer to yourself.');
        }

        return $this->unitOfWork->execute(function () use ($senderUserId, $receiverUserId, $amount, $input) {
            $senderWallet = $this->walletRepository->findByUserIdForUpdate($senderUserId);
            if ($senderWallet === null) {
                throw new WalletNotFoundException($senderUserId->value());
            }

            $receiverWallet = $this->walletRepository->findByUserIdForUpdate($receiverUserId);
            if ($receiverWallet === null) {
                throw new WalletNotFoundException($receiverUserId->value());
            }

            $senderBalanceBefore = $senderWallet->balance();
            $senderWallet->debit($amount);
            $senderBalanceAfter = $senderWallet->balance();

            $receiverBalanceBefore = $receiverWallet->balance();
            $receiverWallet->credit($amount);
            $receiverBalanceAfter = $receiverWallet->balance();

            $senderTx = Transaction::createTransferOut(
                walletId: $senderWallet->id(),
                counterpartWalletId: $receiverWallet->id(),
                amount: $amount,
                balanceBefore: $senderBalanceBefore,
                balanceAfter: $senderBalanceAfter,
                description: $input->description,
            );

            $receiverTx = Transaction::createTransferIn(
                walletId: $receiverWallet->id(),
                counterpartWalletId: $senderWallet->id(),
                amount: $amount,
                balanceBefore: $receiverBalanceBefore,
                balanceAfter: $receiverBalanceAfter,
                relatedTransactionId: $senderTx->id(),
                description: $input->description,
            );

            $this->walletRepository->save($senderWallet);
            $this->walletRepository->save($receiverWallet);
            $this->transactionRepository->save($senderTx);
            $this->transactionRepository->save($receiverTx);

            return new TransferOutputDTO(
                senderTransactionId: $senderTx->id()->value(),
                receiverTransactionId: $receiverTx->id()->value(),
                senderWalletId: $senderWallet->id()->value(),
                receiverWalletId: $receiverWallet->id()->value(),
                amount: $amount->toDecimal(),
                senderBalanceBefore: $senderBalanceBefore->toDecimal(),
                senderBalanceAfter: $senderBalanceAfter->toDecimal(),
                receiverBalanceBefore: $receiverBalanceBefore->toDecimal(),
                receiverBalanceAfter: $receiverBalanceAfter->toDecimal(),
                status: $senderTx->status()->value,
            );
        });
    }
}
