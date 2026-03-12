<?php

declare(strict_types=1);

use App\Domain\Transaction\ValueObjects\TransactionType;
use App\Domain\Transaction\ValueObjects\TransactionStatus;

describe('TransactionType Enum', function () {

    it('has deposit type', function () {
        expect(TransactionType::DEPOSIT->value)->toBe('deposit');
    });

    it('has transfer_in type', function () {
        expect(TransactionType::TRANSFER_IN->value)->toBe('transfer_in');
    });

    it('has transfer_out type', function () {
        expect(TransactionType::TRANSFER_OUT->value)->toBe('transfer_out');
    });

    it('deposit is credit', function () {
        expect(TransactionType::DEPOSIT->isCredit())->toBeTrue();
        expect(TransactionType::DEPOSIT->isDebit())->toBeFalse();
    });

    it('transfer_in is credit', function () {
        expect(TransactionType::TRANSFER_IN->isCredit())->toBeTrue();
        expect(TransactionType::TRANSFER_IN->isDebit())->toBeFalse();
    });

    it('transfer_out is debit', function () {
        expect(TransactionType::TRANSFER_OUT->isDebit())->toBeTrue();
        expect(TransactionType::TRANSFER_OUT->isCredit())->toBeFalse();
    });
});

describe('TransactionStatus Enum', function () {

    it('has completed status', function () {
        expect(TransactionStatus::COMPLETED->value)->toBe('completed');
    });

    it('has reversed status', function () {
        expect(TransactionStatus::REVERSED->value)->toBe('reversed');
    });

    it('has failed status', function () {
        expect(TransactionStatus::FAILED->value)->toBe('failed');
    });

    it('completed can be reversed', function () {
        expect(TransactionStatus::COMPLETED->canBeReversed())->toBeTrue();
    });

    it('reversed cannot be reversed again', function () {
        expect(TransactionStatus::REVERSED->canBeReversed())->toBeFalse();
    });

    it('failed cannot be reversed', function () {
        expect(TransactionStatus::FAILED->canBeReversed())->toBeFalse();
    });

    it('checks isCompleted correctly', function () {
        expect(TransactionStatus::COMPLETED->isCompleted())->toBeTrue();
        expect(TransactionStatus::REVERSED->isCompleted())->toBeFalse();
    });

    it('checks isReversed correctly', function () {
        expect(TransactionStatus::REVERSED->isReversed())->toBeTrue();
        expect(TransactionStatus::COMPLETED->isReversed())->toBeFalse();
    });
});
