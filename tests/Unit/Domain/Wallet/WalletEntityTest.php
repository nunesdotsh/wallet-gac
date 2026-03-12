<?php

declare(strict_types=1);

use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Entities\Wallet;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Domain\Wallet\ValueObjects\Money;
use App\Domain\Wallet\ValueObjects\WalletId;

describe('Wallet Entity', function () {

    describe('creation', function () {
        it('creates with zero balance via factory', function () {
            $userId = UserId::generate();
            $wallet = Wallet::create($userId);

            expect($wallet->balance()->isZero())->toBeTrue();
            expect($wallet->userId()->equals($userId))->toBeTrue();
            expect($wallet->id())->toBeInstanceOf(WalletId::class);
        });

        it('creates with explicit values via constructor', function () {
            $walletId = WalletId::generate();
            $userId = UserId::generate();
            $balance = new Money(5000);

            $wallet = new Wallet($walletId, $userId, $balance);

            expect($wallet->id()->equals($walletId))->toBeTrue();
            expect($wallet->balance()->cents())->toBe(5000);
        });
    });

    describe('credit', function () {
        it('adds money to balance', function () {
            $wallet = Wallet::create(UserId::generate());

            $wallet->credit(new Money(1000));

            expect($wallet->balance()->cents())->toBe(1000);
        });

        it('adds to existing balance', function () {
            $wallet = new Wallet(WalletId::generate(), UserId::generate(), new Money(5000));

            $wallet->credit(new Money(3000));

            expect($wallet->balance()->cents())->toBe(8000);
        });

        it('reduces debt when balance is negative', function () {
            $wallet = new Wallet(WalletId::generate(), UserId::generate(), new Money(-500));

            $wallet->credit(new Money(300));

            expect($wallet->balance()->cents())->toBe(-200);
            expect($wallet->balance()->isNegative())->toBeTrue();
        });

        it('covers negative balance entirely', function () {
            $wallet = new Wallet(WalletId::generate(), UserId::generate(), new Money(-500));

            $wallet->credit(new Money(800));

            expect($wallet->balance()->cents())->toBe(300);
            expect($wallet->balance()->isPositive())->toBeTrue();
        });
    });

    describe('debit', function () {
        it('removes money from balance', function () {
            $wallet = new Wallet(WalletId::generate(), UserId::generate(), new Money(5000));

            $wallet->debit(new Money(2000));

            expect($wallet->balance()->cents())->toBe(3000);
        });

        it('allows debit of entire balance', function () {
            $wallet = new Wallet(WalletId::generate(), UserId::generate(), new Money(5000));

            $wallet->debit(new Money(5000));

            expect($wallet->balance()->isZero())->toBeTrue();
        });

        it('throws on insufficient balance', function () {
            $wallet = new Wallet(WalletId::generate(), UserId::generate(), new Money(1000));

            expect(fn () => $wallet->debit(new Money(2000)))
                ->toThrow(InsufficientBalanceException::class);
        });

        it('throws on zero balance debit attempt', function () {
            $wallet = Wallet::create(UserId::generate());

            expect(fn () => $wallet->debit(new Money(100)))
                ->toThrow(InsufficientBalanceException::class);
        });

        it('throws on negative balance debit attempt', function () {
            $wallet = new Wallet(WalletId::generate(), UserId::generate(), new Money(-500));

            expect(fn () => $wallet->debit(new Money(100)))
                ->toThrow(InsufficientBalanceException::class);
        });
    });

    describe('balance check', function () {
        it('returns true when balance is sufficient', function () {
            $wallet = new Wallet(WalletId::generate(), UserId::generate(), new Money(5000));

            expect($wallet->hasEnoughBalance(new Money(3000)))->toBeTrue();
        });

        it('returns true when balance equals amount', function () {
            $wallet = new Wallet(WalletId::generate(), UserId::generate(), new Money(5000));

            expect($wallet->hasEnoughBalance(new Money(5000)))->toBeTrue();
        });

        it('returns false when balance is insufficient', function () {
            $wallet = new Wallet(WalletId::generate(), UserId::generate(), new Money(1000));

            expect($wallet->hasEnoughBalance(new Money(5000)))->toBeFalse();
        });
    });

    describe('deactivation', function () {
        it('nova carteira está ativa por padrão', function () {
            $wallet = Wallet::create(UserId::generate());

            expect($wallet->isActive())->toBeTrue();
            expect($wallet->deactivatedAt())->toBeNull();
        });

        it('desativa a carteira e registra o momento', function () {
            $wallet = Wallet::create(UserId::generate());

            $wallet->deactivate();

            expect($wallet->isActive())->toBeFalse();
            expect($wallet->deactivatedAt())->not->toBeNull();
            expect($wallet->deactivatedAt())->toBeInstanceOf(DateTimeImmutable::class);
        });

        it('reconstitui carteira desativada com deactivatedAt informado', function () {
            $at     = new DateTimeImmutable('2024-01-01 00:00:00');
            $wallet = new Wallet(WalletId::generate(), UserId::generate(), new Money(0), $at);

            expect($wallet->isActive())->toBeFalse();
            expect($wallet->deactivatedAt())->toBe($at);
        });
    });
});
