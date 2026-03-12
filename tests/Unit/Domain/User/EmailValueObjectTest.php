<?php

declare(strict_types=1);

use App\Domain\User\Exceptions\InvalidEmailException;
use App\Domain\User\ValueObjects\Email;

describe('Email Value Object', function () {

    describe('creation', function () {
        it('creates with valid email', function () {
            $email = new Email('user@example.com');

            expect($email->value())->toBe('user@example.com');
        });

        it('normalizes email to lowercase', function () {
            $email = new Email('User@EXAMPLE.COM');

            expect($email->value())->toBe('user@example.com');
        });

        it('trims whitespace', function () {
            $email = new Email('  user@example.com  ');

            expect($email->value())->toBe('user@example.com');
        });

        it('throws on invalid email format', function () {
            expect(fn () => new Email('not-an-email'))
                ->toThrow(InvalidEmailException::class);
        });

        it('throws on empty string', function () {
            expect(fn () => new Email(''))
                ->toThrow(InvalidEmailException::class);
        });

        it('throws on missing domain', function () {
            expect(fn () => new Email('user@'))
                ->toThrow(InvalidEmailException::class);
        });

        it('throws on missing local part', function () {
            expect(fn () => new Email('@example.com'))
                ->toThrow(InvalidEmailException::class);
        });
    });

    describe('comparison', function () {
        it('equals same email', function () {
            $a = new Email('user@example.com');
            $b = new Email('user@example.com');

            expect($a->equals($b))->toBeTrue();
        });

        it('equals same email with different case', function () {
            $a = new Email('user@example.com');
            $b = new Email('USER@EXAMPLE.COM');

            expect($a->equals($b))->toBeTrue();
        });

        it('does not equal different email', function () {
            $a = new Email('user@example.com');
            $b = new Email('other@example.com');

            expect($a->equals($b))->toBeFalse();
        });
    });

    describe('formatting', function () {
        it('converts to string', function () {
            $email = new Email('user@example.com');

            expect((string) $email)->toBe('user@example.com');
        });
    });
});
