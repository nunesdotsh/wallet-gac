<?php

declare(strict_types=1);

namespace App\Application\UseCases\ReverseTransaction;

use App\Application\Contracts\UnitOfWorkInterface;
use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Exceptions\TransactionAlreadyReversedException;
use App\Domain\Transaction\Exceptions\TransactionNotFoundException;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Transaction\ValueObjects\TransactionId;
use App\Domain\Transaction\ValueObjects\TransactionType;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObjects\Money;

/**
 * Estorna uma transação previamente concluída.
 *
 * Para depósitos: debita o valor depositado de volta da carteira.
 * Para transferências: estorna ambos os lados (credita o remetente, debita o destinatário).
 * Cria transação(ões) de estorno com trilha de auditoria.
 */
final class ReverseTransactionUseCase
{
    public function __construct(
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly WalletRepositoryInterface $walletRepository,
        private readonly UnitOfWorkInterface $unitOfWork,
    ) {}

    /**
     * @param ReverseTransactionInputDTO $input Dados do estorno
     * @return ReverseTransactionOutputDTO Resultado do estorno
     * @throws TransactionNotFoundException Quando a transação não é encontrada
     * @throws TransactionAlreadyReversedException Quando já foi estornada
     * @throws WalletNotFoundException Quando a carteira não é encontrada
     */
    public function execute(ReverseTransactionInputDTO $input): ReverseTransactionOutputDTO
    {
        $transactionId = new TransactionId($input->transactionId);

        return $this->unitOfWork->execute(function () use ($transactionId, $input) {
            $originalTx = $this->transactionRepository->findById($transactionId);

            if ($originalTx === null) {
                throw new TransactionNotFoundException($transactionId->value());
            }

            if (!$originalTx->canBeReversed()) {
                throw new TransactionAlreadyReversedException($transactionId->value());
            }

            $wallet = $this->walletRepository->findByIdForUpdate($originalTx->walletId());
            if ($wallet === null) {
                throw new WalletNotFoundException($originalTx->walletId()->value());
            }

            $balanceBefore = $wallet->balance();
            $reason = $input->reason ?? 'Transaction reversal';

            if ($originalTx->type()->isCredit()) {
                $wallet->debit($originalTx->amount());
            } else {
                $wallet->credit($originalTx->amount());
            }

            $balanceAfter = $wallet->balance();

            $reversalType = $originalTx->type()->isCredit()
                ? TransactionType::TRANSFER_OUT
                : TransactionType::TRANSFER_IN;

            $reversalTx = new Transaction(
                id: TransactionId::generate(),
                walletId: $wallet->id(),
                type: $reversalType,
                amount: $originalTx->amount(),
                balanceBefore: $balanceBefore,
                balanceAfter: $balanceAfter,
                status: \App\Domain\Transaction\ValueObjects\TransactionStatus::COMPLETED,
                relatedTransactionId: $originalTx->id(),
                counterpartWalletId: $originalTx->counterpartWalletId(),
                description: "Reversal: {$reason}",
            );

            $originalTx->markAsReversed();

            if ($originalTx->type() === TransactionType::TRANSFER_OUT && $originalTx->counterpartWalletId() !== null) {
                $counterpartWallet = $this->walletRepository->findByIdForUpdate($originalTx->counterpartWalletId());
                if ($counterpartWallet !== null) {
                    $counterpartWallet->debit($originalTx->amount());
                    $this->walletRepository->save($counterpartWallet);
                }
            } elseif ($originalTx->type() === TransactionType::TRANSFER_IN && $originalTx->counterpartWalletId() !== null) {
                $counterpartWallet = $this->walletRepository->findByIdForUpdate($originalTx->counterpartWalletId());
                if ($counterpartWallet !== null) {
                    $counterpartWallet->credit($originalTx->amount());
                    $this->walletRepository->save($counterpartWallet);
                }
            }

            $this->walletRepository->save($wallet);
            $this->transactionRepository->save($originalTx);
            $this->transactionRepository->save($reversalTx);

            return new ReverseTransactionOutputDTO(
                reversalTransactionId: $reversalTx->id()->value(),
                originalTransactionId: $originalTx->id()->value(),
                walletId: $wallet->id()->value(),
                amount: $originalTx->amount()->toDecimal(),
                balanceBefore: $balanceBefore->toDecimal(),
                balanceAfter: $balanceAfter->toDecimal(),
                status: $reversalTx->status()->value,
            );
        });
    }
}
