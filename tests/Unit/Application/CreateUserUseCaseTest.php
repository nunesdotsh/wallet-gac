<?php

declare(strict_types=1);

use App\Application\Contracts\UnitOfWorkInterface;
use App\Application\UseCases\CreateUser\CreateUserInputDTO;
use App\Application\UseCases\CreateUser\CreateUserUseCase;
use App\Domain\User\Exceptions\DuplicateEmailException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;

describe('CreateUserUseCase', function () {

    beforeEach(function () {
        $this->userRepo = Mockery::mock(UserRepositoryInterface::class);
        $this->walletRepo = Mockery::mock(WalletRepositoryInterface::class);
        $this->unitOfWork = Mockery::mock(UnitOfWorkInterface::class);

        $this->unitOfWork->shouldReceive('execute')
            ->andReturnUsing(fn (callable $cb) => $cb());

        $this->useCase = new CreateUserUseCase(
            $this->userRepo,
            $this->walletRepo,
            $this->unitOfWork,
        );
    });

    it('creates a user and wallet successfully', function () {
        $this->userRepo->shouldReceive('existsByEmail')->once()->andReturn(false);
        $this->userRepo->shouldReceive('save')->once();
        $this->walletRepo->shouldReceive('save')->once();

        $input = new CreateUserInputDTO('John Doe', 'john@example.com', 'secret123');
        $output = $this->useCase->execute($input);

        expect($output->name)->toBe('John Doe');
        expect($output->email)->toBe('john@example.com');
        expect($output->userId)->toBeString()->not->toBeEmpty();
        expect($output->walletId)->toBeString()->not->toBeEmpty();
    });

    it('throws on duplicate email', function () {
        $this->userRepo->shouldReceive('existsByEmail')->once()->andReturn(true);

        $input = new CreateUserInputDTO('John Doe', 'john@example.com', 'secret123');

        expect(fn () => $this->useCase->execute($input))
            ->toThrow(DuplicateEmailException::class);
    });
});
