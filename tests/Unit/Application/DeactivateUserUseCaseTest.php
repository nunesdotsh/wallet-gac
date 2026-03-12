<?php

declare(strict_types=1);

use App\Application\Contracts\UnitOfWorkInterface;
use App\Application\UseCases\DeactivateUser\DeactivateUserInputDTO;
use App\Application\UseCases\DeactivateUser\DeactivateUserUseCase;
use App\Domain\User\Entities\User;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\HashedPassword;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Entities\Wallet;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;

describe('DeactivateUserUseCase', function () {

    beforeEach(function () {
        $this->userRepo   = Mockery::mock(UserRepositoryInterface::class);
        $this->walletRepo = Mockery::mock(WalletRepositoryInterface::class);
        $this->unitOfWork = Mockery::mock(UnitOfWorkInterface::class);

        $this->unitOfWork->shouldReceive('execute')
            ->andReturnUsing(fn (callable $cb) => $cb());

        $this->useCase = new DeactivateUserUseCase(
            $this->userRepo,
            $this->walletRepo,
            $this->unitOfWork,
        );
    });

    it('desativa o usuário e a carteira chamando deactivate() nas entidades', function () {
        $userId = UserId::generate();
        $user   = new User(
            $userId,
            'Test User',
            new Email('test@example.com'),
            HashedPassword::fromPlain('secret123'),
        );
        $wallet = Wallet::create($userId);

        $this->userRepo->shouldReceive('findById')->once()->andReturn($user);
        $this->walletRepo->shouldReceive('findByUserId')->once()->andReturn($wallet);

        $this->walletRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(fn (Wallet $w) => ! $w->isActive()));

        $this->userRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(fn (User $u) => ! $u->isActive()));

        $this->useCase->execute(new DeactivateUserInputDTO(userId: $userId->value()));

        expect($user->isActive())->toBeFalse();
        expect($user->deactivatedAt())->not->toBeNull();
        expect($wallet->isActive())->toBeFalse();
        expect($wallet->deactivatedAt())->not->toBeNull();
    });

    it('desativa usuário sem carteira sem erros', function () {
        $userId = UserId::generate();
        $user   = new User(
            $userId,
            'Test User',
            new Email('test@example.com'),
            HashedPassword::fromPlain('secret123'),
        );

        $this->userRepo->shouldReceive('findById')->once()->andReturn($user);
        $this->walletRepo->shouldReceive('findByUserId')->once()->andReturn(null);

        $this->walletRepo->shouldNotReceive('save');

        $this->userRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::on(fn (User $u) => ! $u->isActive()));

        $this->useCase->execute(new DeactivateUserInputDTO(userId: $userId->value()));

        expect($user->isActive())->toBeFalse();
    });

    it('lança UserNotFoundException quando usuário não existe', function () {
        $userId = UserId::generate();

        $this->userRepo->shouldReceive('findById')->once()->andReturn(null);

        expect(fn () => $this->useCase->execute(new DeactivateUserInputDTO(userId: $userId->value())))
            ->toThrow(UserNotFoundException::class);
    });

    it('não chama delete() no repositório — estado é mutado na entidade', function () {
        $userId = UserId::generate();
        $user   = new User(
            $userId,
            'Test User',
            new Email('test@example.com'),
            HashedPassword::fromPlain('secret123'),
        );

        $this->userRepo->shouldReceive('findById')->once()->andReturn($user);
        $this->walletRepo->shouldReceive('findByUserId')->once()->andReturn(null);
        $this->userRepo->shouldReceive('save')->once();

        $this->userRepo->shouldNotReceive('delete');
        $this->walletRepo->shouldNotReceive('delete');

        $this->useCase->execute(new DeactivateUserInputDTO(userId: $userId->value()));
    });
});
