<?php

declare(strict_types=1);

use App\Application\UseCases\GetTransactionHistory\GetTransactionHistoryUseCase;
use App\Application\UseCases\GetTransactionHistory\TransactionHistoryItemDTO;
use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Transaction\ValueObjects\TransactionId;
use App\Domain\Transaction\ValueObjects\TransactionStatus;
use App\Domain\Transaction\ValueObjects\TransactionType;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\HashedPassword;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Entities\Wallet;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletId;

describe('GetTransactionHistoryUseCase', function () {

    beforeEach(function () {
        $this->walletRepo  = Mockery::mock(WalletRepositoryInterface::class);
        $this->txRepo      = Mockery::mock(TransactionRepositoryInterface::class);
        $this->userRepo    = Mockery::mock(UserRepositoryInterface::class);

        $this->useCase = new GetTransactionHistoryUseCase(
            $this->walletRepo,
            $this->txRepo,
            $this->userRepo,
        );

        $this->walletId1    = '11111111-1111-4111-a111-111111111111';
        $this->walletId2    = '22222222-2222-4222-a222-222222222222';
        $this->userId1      = 'aaaaaaaa-aaaa-4aaa-aaaa-aaaaaaaaaaaa';
        $this->userId2      = 'bbbbbbbb-bbbb-4bbb-bbbb-bbbbbbbbbbbb';
        $this->txId1        = 'cccccccc-cccc-4ccc-cccc-cccccccccccc';
        $this->txId2        = 'dddddddd-dddd-4ddd-dddd-dddddddddddd';
        $this->txId3        = 'eeeeeeee-eeee-4eee-eeee-eeeeeeeeeeee';
    });

    it('lança exceção quando carteira não existe', function () {
        $this->walletRepo
            ->shouldReceive('findByUserId')
            ->once()
            ->andReturn(null);

        expect(fn () => $this->useCase->execute($this->userId1))
            ->toThrow(WalletNotFoundException::class);
    });

    it('retorna lista vazia quando não há transações', function () {
        $wallet = new Wallet(new WalletId($this->walletId1), new UserId($this->userId1), new Money(0));

        $this->walletRepo->shouldReceive('findByUserId')->once()->andReturn($wallet);
        $this->txRepo->shouldReceive('findByWalletId')->once()->andReturn([]);

        $result = $this->useCase->execute($this->userId1);

        expect($result)->toBeArray()->toBeEmpty();
    });

    it('retorna transação de depósito sem contrapartida', function () {
        $wallet = new Wallet(new WalletId($this->walletId1), new UserId($this->userId1), new Money(10000));
        $tx     = new Transaction(
            id: new TransactionId($this->txId1),
            walletId: new WalletId($this->walletId1),
            type: TransactionType::DEPOSIT,
            amount: new Money(10000),
            balanceBefore: new Money(0),
            balanceAfter: new Money(10000),
            status: TransactionStatus::COMPLETED,
        );

        $this->walletRepo->shouldReceive('findByUserId')->once()->andReturn($wallet);
        $this->txRepo->shouldReceive('findByWalletId')->once()->andReturn([$tx]);

        $result = $this->useCase->execute($this->userId1);

        expect($result)->toHaveCount(1);
        expect($result[0])->toBeInstanceOf(TransactionHistoryItemDTO::class);
        expect($result[0]->counterpartName)->toBeNull();
        expect($result[0]->counterpartEmail)->toBeNull();
    });

    it('preenche nome e e-mail da contrapartida em transferência', function () {
        $senderWallet   = new Wallet(new WalletId($this->walletId1), new UserId($this->userId1), new Money(50000));
        $receiverWallet = new Wallet(new WalletId($this->walletId2), new UserId($this->userId2), new Money(10000));
        $receiverUser   = new User(
            new UserId($this->userId2),
            'Maria Silva',
            new Email('maria@example.com'),
            HashedPassword::fromHash('hash'),
        );

        $tx = new Transaction(
            id: new TransactionId($this->txId1),
            walletId: new WalletId($this->walletId1),
            type: TransactionType::TRANSFER_OUT,
            amount: new Money(5000),
            balanceBefore: new Money(50000),
            balanceAfter: new Money(45000),
            status: TransactionStatus::COMPLETED,
            counterpartWalletId: new WalletId($this->walletId2),
        );

        $this->walletRepo->shouldReceive('findByUserId')->once()->andReturn($senderWallet);
        $this->txRepo->shouldReceive('findByWalletId')->once()->andReturn([$tx]);
        $this->walletRepo->shouldReceive('findById')
            ->with(Mockery::on(fn ($id) => $id->value() === $this->walletId2))
            ->once()
            ->andReturn($receiverWallet);
        $this->userRepo->shouldReceive('findById')
            ->with(Mockery::on(fn ($id) => $id->value() === $this->userId2))
            ->once()
            ->andReturn($receiverUser);

        $result = $this->useCase->execute($this->userId1);

        expect($result[0]->counterpartName)->toBe('Maria Silva');
        expect($result[0]->counterpartEmail)->toBe('maria@example.com');
    });

    it('deduplica lookups de contrapartida para múltiplas transferências ao mesmo destinatário', function () {
        $senderWallet   = new Wallet(new WalletId($this->walletId1), new UserId($this->userId1), new Money(100000));
        $receiverWallet = new Wallet(new WalletId($this->walletId2), new UserId($this->userId2), new Money(0));
        $receiverUser   = new User(
            new UserId($this->userId2),
            'João Costa',
            new Email('joao@example.com'),
            HashedPassword::fromHash('hash'),
        );

        $makeTx = fn (string $txId) => new Transaction(
            id: new TransactionId($txId),
            walletId: new WalletId($this->walletId1),
            type: TransactionType::TRANSFER_OUT,
            amount: new Money(1000),
            balanceBefore: new Money(100000),
            balanceAfter: new Money(99000),
            status: TransactionStatus::COMPLETED,
            counterpartWalletId: new WalletId($this->walletId2),
        );

        $this->walletRepo->shouldReceive('findByUserId')->once()->andReturn($senderWallet);
        $this->txRepo->shouldReceive('findByWalletId')->once()->andReturn([
            $makeTx($this->txId1),
            $makeTx($this->txId2),
            $makeTx($this->txId3),
        ]);

        // findById da wallet e findById do user chamados apenas 1 vez (cache)
        $this->walletRepo->shouldReceive('findById')->once()->andReturn($receiverWallet);
        $this->userRepo->shouldReceive('findById')->once()->andReturn($receiverUser);

        $result = $this->useCase->execute($this->userId1);

        expect($result)->toHaveCount(3);
        foreach ($result as $dto) {
            expect($dto->counterpartName)->toBe('João Costa');
            expect($dto->counterpartEmail)->toBe('joao@example.com');
        }
    });
});
