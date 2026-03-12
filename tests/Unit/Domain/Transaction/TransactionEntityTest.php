<?php

declare(strict_types=1);

use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Exceptions\TransactionAlreadyReversedException;
use App\Domain\Transaction\ValueObjects\TransactionId;
use App\Domain\Transaction\ValueObjects\TransactionStatus;
use App\Domain\Transaction\ValueObjects\TransactionType;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletId;

describe('Transaction Entity', function () {

    describe('deposit creation', function () {
        it('creates a deposit transaction', function () {
            $walletId = WalletId::generate();
            $amount = new Money(1000);
            $balanceBefore = new Money(5000);
            $balanceAfter = new Money(6000);

            $tx = Transaction::createDeposit($walletId, $amount, $balanceBefore, $balanceAfter, 'Test deposit');

            expect($tx->id())->toBeInstanceOf(TransactionId::class);
            expect($tx->walletId()->equals($walletId))->toBeTrue();
            expect($tx->type())->toBe(TransactionType::DEPOSIT);
            expect($tx->amount()->cents())->toBe(1000);
            expect($tx->balanceBefore()->cents())->toBe(5000);
            expect($tx->balanceAfter()->cents())->toBe(6000);
            expect($tx->status())->toBe(TransactionStatus::COMPLETED);
            expect($tx->description())->toBe('Test deposit');
            expect($tx->counterpartWalletId())->toBeNull();
            expect($tx->relatedTransactionId())->toBeNull();
        });
    });

    describe('transfer creation', function () {
        it('creates a transfer-out transaction', function () {
            $senderWalletId = WalletId::generate();
            $receiverWalletId = WalletId::generate();
            $amount = new Money(500);

            $tx = Transaction::createTransferOut(
                $senderWalletId,
                $receiverWalletId,
                $amount,
                new Money(2000),
                new Money(1500),
            );

            expect($tx->type())->toBe(TransactionType::TRANSFER_OUT);
            expect($tx->walletId()->equals($senderWalletId))->toBeTrue();
            expect($tx->counterpartWalletId()->equals($receiverWalletId))->toBeTrue();
            expect($tx->status())->toBe(TransactionStatus::COMPLETED);
        });

        it('creates a transfer-in transaction linked to transfer-out', function () {
            $senderWalletId = WalletId::generate();
            $receiverWalletId = WalletId::generate();
            $relatedTxId = TransactionId::generate();
            $amount = new Money(500);

            $tx = Transaction::createTransferIn(
                $receiverWalletId,
                $senderWalletId,
                $amount,
                new Money(3000),
                new Money(3500),
                $relatedTxId,
            );

            expect($tx->type())->toBe(TransactionType::TRANSFER_IN);
            expect($tx->walletId()->equals($receiverWalletId))->toBeTrue();
            expect($tx->counterpartWalletId()->equals($senderWalletId))->toBeTrue();
            expect($tx->relatedTransactionId()->equals($relatedTxId))->toBeTrue();
        });
    });

    describe('reversal', function () {
        it('marks a completed transaction as reversed', function () {
            $tx = Transaction::createDeposit(
                WalletId::generate(),
                new Money(1000),
                new Money(0),
                new Money(1000),
            );

            expect($tx->canBeReversed())->toBeTrue();

            $tx->markAsReversed();

            expect($tx->status())->toBe(TransactionStatus::REVERSED);
            expect($tx->reversedAt())->not->toBeNull();
            expect($tx->canBeReversed())->toBeFalse();
        });

        it('throws when trying to reverse an already reversed transaction', function () {
            $tx = Transaction::createDeposit(
                WalletId::generate(),
                new Money(1000),
                new Money(0),
                new Money(1000),
            );

            $tx->markAsReversed();

            expect(fn () => $tx->markAsReversed())
                ->toThrow(TransactionAlreadyReversedException::class);
        });
    });

    describe('accessors', function () {
        it('returns creation timestamp', function () {
            $tx = Transaction::createDeposit(
                WalletId::generate(),
                new Money(1000),
                new Money(0),
                new Money(1000),
            );

            expect($tx->createdAt())->toBeInstanceOf(DateTimeImmutable::class);
        });

        it('returns null for reversedAt when not reversed', function () {
            $tx = Transaction::createDeposit(
                WalletId::generate(),
                new Money(1000),
                new Money(0),
                new Money(1000),
            );

            expect($tx->reversedAt())->toBeNull();
        });
    });
});
