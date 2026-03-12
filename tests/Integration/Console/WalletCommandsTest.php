<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('wallet:create-user command', function () {

    it('creates a user interactively', function () {
        $this->artisan('wallet:create-user', [
            '--name' => 'John Doe',
            '--email' => 'john@example.com',
            '--password' => 'secret123',
        ])
            ->expectsOutput('User created successfully!')
            ->assertSuccessful();
    });

    it('fails on duplicate email', function () {
        $this->artisan('wallet:create-user', [
            '--name' => 'John Doe',
            '--email' => 'john@example.com',
            '--password' => 'secret123',
        ])->assertSuccessful();

        $this->artisan('wallet:create-user', [
            '--name' => 'Jane Doe',
            '--email' => 'john@example.com',
            '--password' => 'secret456',
        ])->assertFailed();
    });

    it('fails on invalid email', function () {
        $this->artisan('wallet:create-user', [
            '--name' => 'John Doe',
            '--email' => 'not-an-email',
            '--password' => 'secret123',
        ])->assertFailed();
    });
});

describe('wallet:deposit command', function () {

    it('deposits money into a wallet', function () {
        $this->artisan('wallet:create-user', [
            '--name' => 'John Doe',
            '--email' => 'john@example.com',
            '--password' => 'secret123',
        ])->assertSuccessful();

        $this->artisan('wallet:deposit', [
            '--email' => 'john@example.com',
            '--amount' => '100.50',
        ])
            ->expectsOutput('Deposit completed successfully!')
            ->assertSuccessful();
    });

    it('fails for non-existent user', function () {
        $this->artisan('wallet:deposit', [
            '--email' => 'nobody@example.com',
            '--amount' => '100.00',
        ])->assertFailed();
    });
});

describe('wallet:balance command', function () {

    it('shows wallet balance', function () {
        $this->artisan('wallet:create-user', [
            '--name' => 'John Doe',
            '--email' => 'john@example.com',
            '--password' => 'secret123',
        ])->assertSuccessful();

        $this->artisan('wallet:deposit', [
            '--email' => 'john@example.com',
            '--amount' => '250.00',
        ])->assertSuccessful();

        $this->artisan('wallet:balance', [
            '--email' => 'john@example.com',
        ])->assertSuccessful();
    });
});

describe('wallet:transfer command', function () {

    it('transfers money between users', function () {
        $this->artisan('wallet:create-user', [
            '--name' => 'Sender',
            '--email' => 'sender@example.com',
            '--password' => 'secret123',
        ])->assertSuccessful();

        $this->artisan('wallet:create-user', [
            '--name' => 'Receiver',
            '--email' => 'receiver@example.com',
            '--password' => 'secret123',
        ])->assertSuccessful();

        $this->artisan('wallet:deposit', [
            '--email' => 'sender@example.com',
            '--amount' => '500.00',
        ])->assertSuccessful();

        $this->artisan('wallet:transfer', [
            '--from' => 'sender@example.com',
            '--to' => 'receiver@example.com',
            '--amount' => '200.00',
        ])
            ->expectsOutput('Transfer completed successfully!')
            ->assertSuccessful();
    });

    it('fails on insufficient balance', function () {
        $this->artisan('wallet:create-user', [
            '--name' => 'Sender',
            '--email' => 'sender@example.com',
            '--password' => 'secret123',
        ])->assertSuccessful();

        $this->artisan('wallet:create-user', [
            '--name' => 'Receiver',
            '--email' => 'receiver@example.com',
            '--password' => 'secret123',
        ])->assertSuccessful();

        $this->artisan('wallet:transfer', [
            '--from' => 'sender@example.com',
            '--to' => 'receiver@example.com',
            '--amount' => '100.00',
        ])->assertFailed();
    });
});

describe('wallet:reverse command', function () {

    it('reverses a deposit transaction', function () {
        $this->artisan('wallet:create-user', [
            '--name' => 'John Doe',
            '--email' => 'john@example.com',
            '--password' => 'secret123',
        ])->assertSuccessful();

        $this->artisan('wallet:deposit', [
            '--email' => 'john@example.com',
            '--amount' => '100.00',
        ])->assertSuccessful();

        $txId = \App\Infrastructure\Persistence\Eloquent\Models\TransactionModel::first()->id;

        $this->artisan('wallet:reverse', [
            '--transaction-id' => $txId,
            '--reason' => 'Test reversal',
        ])
            ->expectsConfirmation(
                "Are you sure you want to reverse transaction {$txId}?",
                'yes',
            )
            ->expectsOutput('Transaction reversed successfully!')
            ->assertSuccessful();
    });
});

describe('wallet:history command', function () {

    it('shows transaction history', function () {
        $this->artisan('wallet:create-user', [
            '--name' => 'John Doe',
            '--email' => 'john@example.com',
            '--password' => 'secret123',
        ])->assertSuccessful();

        $this->artisan('wallet:deposit', [
            '--email' => 'john@example.com',
            '--amount' => '100.00',
        ])->assertSuccessful();

        $this->artisan('wallet:deposit', [
            '--email' => 'john@example.com',
            '--amount' => '50.00',
        ])->assertSuccessful();

        $this->artisan('wallet:history', [
            '--email' => 'john@example.com',
        ])->assertSuccessful();
    });

    it('shows empty history message', function () {
        $this->artisan('wallet:create-user', [
            '--name' => 'John Doe',
            '--email' => 'john@example.com',
            '--password' => 'secret123',
        ])->assertSuccessful();

        $this->artisan('wallet:history', [
            '--email' => 'john@example.com',
        ])
            ->expectsOutput('No transactions found for john@example.com.')
            ->assertSuccessful();
    });
});
