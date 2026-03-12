<?php

declare(strict_types=1);

use App\Application\UseCases\GetBalance\GetBalanceUseCase;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Entities\Wallet;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletId;

describe('GetBalanceUseCase', function () {

    beforeEach(function () {
        $this->walletRepo = Mockery::mock(WalletRepositoryInterface::class);
        $this->useCase = new GetBalanceUseCase($this->walletRepo);
    });

    it('returns balance for existing wallet', function () {
        $userId = UserId::generate();
        $wallet = new Wallet(WalletId::generate(), $userId, new Money(15000));

        $this->walletRepo->shouldReceive('findByUserId')
            ->once()->andReturn($wallet);

        $output = $this->useCase->execute($userId->value());

        expect($output->balance)->toBe('150.00');
        expect($output->userId)->toBe($userId->value());
    });

    it('throws on wallet not found', function () {
        $this->walletRepo->shouldReceive('findByUserId')
            ->once()->andReturn(null);

        $userId = UserId::generate();

        expect(fn () => $this->useCase->execute($userId->value()))
            ->toThrow(WalletNotFoundException::class);
    });
});
