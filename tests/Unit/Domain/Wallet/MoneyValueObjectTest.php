<?php

declare(strict_types=1);

use App\Domain\Wallet\ValueObjects\Money;

describe('Money Value Object', function () {

    describe('creation', function () {
        it('creates from cents', function () {
            $money = new Money(1050);

            expect($money->cents())->toBe(1050);
            expect($money->toDecimal())->toBe('10.50');
        });

        it('creates from decimal string', function () {
            $money = Money::fromDecimal('10.50');

            expect($money->cents())->toBe(1050);
        });

        it('creates zero value', function () {
            $money = Money::zero();

            expect($money->cents())->toBe(0);
            expect($money->isZero())->toBeTrue();
        });

        it('handles whole numbers without decimals', function () {
            $money = Money::fromDecimal('100');

            expect($money->cents())->toBe(10000);
            expect($money->toDecimal())->toBe('100.00');
        });

        it('handles single decimal place', function () {
            $money = Money::fromDecimal('10.5');

            expect($money->cents())->toBe(1050);
        });

        it('allows negative values for balance representation', function () {
            $money = new Money(-500);

            expect($money->cents())->toBe(-500);
            expect($money->isNegative())->toBeTrue();
            expect($money->toDecimal())->toBe('-5.00');
        });
    });

    describe('arithmetic', function () {
        it('adds two money values', function () {
            $a = new Money(1000);
            $b = new Money(500);

            $result = $a->add($b);

            expect($result->cents())->toBe(1500);
        });

        it('subtracts two money values', function () {
            $a = new Money(1000);
            $b = new Money(300);

            $result = $a->subtract($b);

            expect($result->cents())->toBe(700);
        });

        it('handles subtraction resulting in negative', function () {
            $a = new Money(300);
            $b = new Money(1000);

            $result = $a->subtract($b);

            expect($result->cents())->toBe(-700);
            expect($result->isNegative())->toBeTrue();
        });

        it('adds to negative balance correctly', function () {
            $negative = new Money(-500);
            $deposit = new Money(300);

            $result = $negative->add($deposit);

            expect($result->cents())->toBe(-200);
            expect($result->isNegative())->toBeTrue();
        });

        it('deposit covers negative balance entirely', function () {
            $negative = new Money(-500);
            $deposit = new Money(800);

            $result = $negative->add($deposit);

            expect($result->cents())->toBe(300);
            expect($result->isPositive())->toBeTrue();
        });

        it('is immutable - operations return new instances', function () {
            $original = new Money(1000);
            $result = $original->add(new Money(500));

            expect($original->cents())->toBe(1000);
            expect($result->cents())->toBe(1500);
        });
    });

    describe('comparisons', function () {
        it('checks positive', function () {
            expect((new Money(100))->isPositive())->toBeTrue();
            expect((new Money(0))->isPositive())->toBeFalse();
            expect((new Money(-100))->isPositive())->toBeFalse();
        });

        it('checks negative', function () {
            expect((new Money(-100))->isNegative())->toBeTrue();
            expect((new Money(0))->isNegative())->toBeFalse();
            expect((new Money(100))->isNegative())->toBeFalse();
        });

        it('checks zero', function () {
            expect((new Money(0))->isZero())->toBeTrue();
            expect((new Money(100))->isZero())->toBeFalse();
        });

        it('checks greater than or equal', function () {
            $a = new Money(1000);
            $b = new Money(500);
            $c = new Money(1000);

            expect($a->isGreaterThanOrEqual($b))->toBeTrue();
            expect($a->isGreaterThanOrEqual($c))->toBeTrue();
            expect($b->isGreaterThanOrEqual($a))->toBeFalse();
        });

        it('checks greater than', function () {
            $a = new Money(1000);
            $b = new Money(500);
            $c = new Money(1000);

            expect($a->isGreaterThan($b))->toBeTrue();
            expect($a->isGreaterThan($c))->toBeFalse();
        });

        it('checks equality', function () {
            $a = new Money(1000);
            $b = new Money(1000);
            $c = new Money(500);

            expect($a->equals($b))->toBeTrue();
            expect($a->equals($c))->toBeFalse();
        });
    });

    describe('formatting', function () {
        it('converts to decimal string', function () {
            expect((new Money(1050))->toDecimal())->toBe('10.50');
            expect((new Money(100))->toDecimal())->toBe('1.00');
            expect((new Money(1))->toDecimal())->toBe('0.01');
            expect((new Money(0))->toDecimal())->toBe('0.00');
        });

        it('converts to string via __toString', function () {
            $money = new Money(1050);

            expect((string) $money)->toBe('10.50');
        });
    });
});
