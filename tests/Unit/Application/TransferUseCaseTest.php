<?php

declare(strict_types=1);

use App\Application\Contracts\UnitOfWorkInterface;
use App\Application\UseCases\Transfer\TransferInputDTO;
use App\Application\UseCases\Transfer\TransferUseCase;
use App\Domain\Transaction\Exceptions\InvalidTransactionException;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Entities\Wallet;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Domain\Wallet\Exceptions\NegativeAmountException;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletId;

describe('TransferUseCase', function () {

    beforeEach(function () {
        $this->walletRepo = Mockery::mock(WalletRepositoryInterface::class);
        $this->txRepo = Mockery::mock(TransactionRepositoryInterface::class);
        $this->unitOfWork = Mockery::mock(UnitOfWorkInterface::class);

        $this->unitOfWork->shouldReceive('execute')
            ->andReturnUsing(fn (callable $cb) => $cb());

        $this->useCase = new TransferUseCase(
            $this->walletRepo,
            $this->txRepo,
            $this->unitOfWork,
        );
    });

    it('transfers money between wallets successfully', function () {
        $senderId = UserId::generate();
        $receiverId = UserId::generate();
        $senderWallet = new Wallet(WalletId::generate(), $senderId, new Money(10000));
        $receiverWallet = new Wallet(WalletId::generate(), $receiverId, new Money(5000));

        $this->walletRepo->shouldReceive('findByUserIdForUpdate')
            ->with(Mockery::on(fn ($id) => $id->equals($senderId)))
            ->once()->andReturn($senderWallet);
        $this->walletRepo->shouldReceive('findByUserIdForUpdate')
            ->with(Mockery::on(fn ($id) => $id->equals($receiverId)))
            ->once()->andReturn($receiverWallet);
        $this->walletRepo->shouldReceive('save')->twice();
        $this->txRepo->shouldReceive('save')->twice();

        $input = new TransferInputDTO($senderId->value(), $receiverId->value(), '50.00');
        $output = $this->useCase->execute($input);

        expect($output->amount)->toBe('50.00');
        expect($output->senderBalanceBefore)->toBe('100.00');
        expect($output->senderBalanceAfter)->toBe('50.00');
        expect($output->receiverBalanceBefore)->toBe('50.00');
        expect($output->receiverBalanceAfter)->toBe('100.00');
        expect($output->status)->toBe('completed');
    });

    it('throws on self-transfer', function () {
        $userId = UserId::generate();
        $input = new TransferInputDTO($userId->value(), $userId->value(), '50.00');

        expect(fn () => $this->useCase->execute($input))
            ->toThrow(InvalidTransactionException::class);
    });

    it('throws on insufficient balance', function () {
        $senderId = UserId::generate();
        $receiverId = UserId::generate();
        $senderWallet = new Wallet(WalletId::generate(), $senderId, new Money(1000));
        $receiverWallet = new Wallet(WalletId::generate(), $receiverId, new Money(5000));

        $this->walletRepo->shouldReceive('findByUserIdForUpdate')
            ->with(Mockery::on(fn ($id) => $id->equals($senderId)))
            ->once()->andReturn($senderWallet);
        $this->walletRepo->shouldReceive('findByUserIdForUpdate')
            ->with(Mockery::on(fn ($id) => $id->equals($receiverId)))
            ->once()->andReturn($receiverWallet);

        $input = new TransferInputDTO($senderId->value(), $receiverId->value(), '500.00');

        expect(fn () => $this->useCase->execute($input))
            ->toThrow(InsufficientBalanceException::class);
    });

    it('throws on sender wallet not found', function () {
        $senderId = UserId::generate();
        $receiverId = UserId::generate();

        $this->walletRepo->shouldReceive('findByUserIdForUpdate')
            ->with(Mockery::on(fn ($id) => $id->equals($senderId)))
            ->once()->andReturn(null);

        $input = new TransferInputDTO($senderId->value(), $receiverId->value(), '50.00');

        expect(fn () => $this->useCase->execute($input))
            ->toThrow(WalletNotFoundException::class);
    });

    it('throws on receiver wallet not found', function () {
        $senderId = UserId::generate();
        $receiverId = UserId::generate();
        $senderWallet = new Wallet(WalletId::generate(), $senderId, new Money(10000));

        $this->walletRepo->shouldReceive('findByUserIdForUpdate')
            ->with(Mockery::on(fn ($id) => $id->equals($senderId)))
            ->once()->andReturn($senderWallet);
        $this->walletRepo->shouldReceive('findByUserIdForUpdate')
            ->with(Mockery::on(fn ($id) => $id->equals($receiverId)))
            ->once()->andReturn(null);

        $input = new TransferInputDTO($senderId->value(), $receiverId->value(), '50.00');

        expect(fn () => $this->useCase->execute($input))
            ->toThrow(WalletNotFoundException::class);
    });

    it('throws on zero amount', function () {
        $input = new TransferInputDTO(
            UserId::generate()->value(),
            UserId::generate()->value(),
            '0.00',
        );

        expect(fn () => $this->useCase->execute($input))
            ->toThrow(NegativeAmountException::class);
    });
});
