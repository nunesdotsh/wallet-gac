<?php

declare(strict_types=1);

use App\Application\Contracts\UnitOfWorkInterface;
use App\Application\UseCases\Deposit\DepositInputDTO;
use App\Application\UseCases\Deposit\DepositUseCase;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Entities\Wallet;
use App\Domain\Wallet\Exceptions\NegativeAmountException;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObjects\Money;

describe('DepositUseCase', function () {

    beforeEach(function () {
        $this->walletRepo = Mockery::mock(WalletRepositoryInterface::class);
        $this->txRepo = Mockery::mock(TransactionRepositoryInterface::class);
        $this->unitOfWork = Mockery::mock(UnitOfWorkInterface::class);

        $this->unitOfWork->shouldReceive('execute')
            ->andReturnUsing(fn (callable $cb) => $cb());

        $this->useCase = new DepositUseCase(
            $this->walletRepo,
            $this->txRepo,
            $this->unitOfWork,
        );
    });

    it('deposits money successfully', function () {
        $userId = UserId::generate();
        $wallet = Wallet::create($userId);

        $this->walletRepo->shouldReceive('findByUserIdForUpdate')
            ->once()->andReturn($wallet);
        $this->walletRepo->shouldReceive('save')->once();
        $this->txRepo->shouldReceive('save')->once();

        $input = new DepositInputDTO($userId->value(), '100.50');
        $output = $this->useCase->execute($input);

        expect($output->amount)->toBe('100.50');
        expect($output->balanceBefore)->toBe('0.00');
        expect($output->balanceAfter)->toBe('100.50');
        expect($output->status)->toBe('completed');
    });

    it('deposits to negative balance reduces debt', function () {
        $userId = UserId::generate();
        $wallet = new Wallet(
            \App\Domain\Wallet\ValueObjects\WalletId::generate(),
            $userId,
            new Money(-500),
        );

        $this->walletRepo->shouldReceive('findByUserIdForUpdate')
            ->once()->andReturn($wallet);
        $this->walletRepo->shouldReceive('save')->once();
        $this->txRepo->shouldReceive('save')->once();

        $input = new DepositInputDTO($userId->value(), '3.00');
        $output = $this->useCase->execute($input);

        expect($output->balanceBefore)->toBe('-5.00');
        expect($output->balanceAfter)->toBe('-2.00');
    });

    it('throws on wallet not found', function () {
        $this->walletRepo->shouldReceive('findByUserIdForUpdate')
            ->once()->andReturn(null);

        $userId = UserId::generate();
        $input = new DepositInputDTO($userId->value(), '100.00');

        expect(fn () => $this->useCase->execute($input))
            ->toThrow(WalletNotFoundException::class);
    });

    it('throws on zero amount', function () {
        $input = new DepositInputDTO(UserId::generate()->value(), '0.00');

        expect(fn () => $this->useCase->execute($input))
            ->toThrow(NegativeAmountException::class);
    });

    it('throws on negative amount', function () {
        $input = new DepositInputDTO(UserId::generate()->value(), '-10.00');

        expect(fn () => $this->useCase->execute($input))
            ->toThrow(NegativeAmountException::class);
    });
});
