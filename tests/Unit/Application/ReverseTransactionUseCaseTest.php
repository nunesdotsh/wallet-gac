<?php

declare(strict_types=1);

use App\Application\Contracts\UnitOfWorkInterface;
use App\Application\UseCases\ReverseTransaction\ReverseTransactionInputDTO;
use App\Application\UseCases\ReverseTransaction\ReverseTransactionUseCase;
use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Exceptions\TransactionAlreadyReversedException;
use App\Domain\Transaction\Exceptions\TransactionNotFoundException;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Transaction\ValueObjects\TransactionId;
use App\Domain\Wallet\Entities\Wallet;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletId;
use App\Domain\User\ValueObjects\UserId;

describe('ReverseTransactionUseCase', function () {

    beforeEach(function () {
        $this->txRepo = Mockery::mock(TransactionRepositoryInterface::class);
        $this->walletRepo = Mockery::mock(WalletRepositoryInterface::class);
        $this->unitOfWork = Mockery::mock(UnitOfWorkInterface::class);

        $this->unitOfWork->shouldReceive('execute')
            ->andReturnUsing(fn (callable $cb) => $cb());

        $this->useCase = new ReverseTransactionUseCase(
            $this->txRepo,
            $this->walletRepo,
            $this->unitOfWork,
        );
    });

    it('reverses a deposit transaction', function () {
        $walletId = WalletId::generate();
        $wallet = new Wallet($walletId, UserId::generate(), new Money(10000));
        $depositTx = Transaction::createDeposit($walletId, new Money(5000), new Money(5000), new Money(10000));

        $this->txRepo->shouldReceive('findById')->once()->andReturn($depositTx);
        $this->walletRepo->shouldReceive('findByIdForUpdate')->once()->andReturn($wallet);
        $this->walletRepo->shouldReceive('save')->once();
        $this->txRepo->shouldReceive('save')->twice();

        $input = new ReverseTransactionInputDTO($depositTx->id()->value(), 'Customer request');
        $output = $this->useCase->execute($input);

        expect($output->originalTransactionId)->toBe($depositTx->id()->value());
        expect($output->amount)->toBe('50.00');
        expect($output->balanceBefore)->toBe('100.00');
        expect($output->balanceAfter)->toBe('50.00');
        expect($output->status)->toBe('completed');
    });

    it('throws on transaction not found', function () {
        $this->txRepo->shouldReceive('findById')->once()->andReturn(null);

        $txId = TransactionId::generate();
        $input = new ReverseTransactionInputDTO($txId->value());

        expect(fn () => $this->useCase->execute($input))
            ->toThrow(TransactionNotFoundException::class);
    });

    it('throws on already reversed transaction', function () {
        $walletId = WalletId::generate();
        $depositTx = Transaction::createDeposit($walletId, new Money(5000), new Money(0), new Money(5000));
        $depositTx->markAsReversed();

        $this->txRepo->shouldReceive('findById')->once()->andReturn($depositTx);

        $input = new ReverseTransactionInputDTO($depositTx->id()->value());

        expect(fn () => $this->useCase->execute($input))
            ->toThrow(TransactionAlreadyReversedException::class);
    });
});
