<?php

declare(strict_types=1);

/**
 * Testes de funcionalidade para reversão de transações.
 *
 * Verifica a reversão de depósitos, controle de acesso,
 * tentativas de reversão duplicada e transações inexistentes.
 */

use App\Application\UseCases\Deposit\DepositInputDTO;
use App\Application\UseCases\Deposit\DepositUseCase;
use App\Infrastructure\Persistence\Eloquent\Models\WalletModel;
use App\Models\User;

test('redireciona para login ao reverter transação sem autenticação', function () {
    $response = $this->post(route('transactions.reverse', 'qualquer-id'));

    $response->assertRedirect(route('login'));
});

test('reverte transação de depósito com sucesso', function () {
    $user = User::factory()->create();
    WalletModel::create(['user_id' => $user->id, 'balance' => '0.00']);

    $depositUseCase = app(DepositUseCase::class);
    $result = $depositUseCase->execute(new DepositInputDTO(
        userId: $user->id,
        amount: '100.00',
    ));

    $wallet = WalletModel::where('user_id', $user->id)->first();
    expect((float) $wallet->balance)->toBe(100.0);

    $response = $this->actingAs($user)
        ->post(route('transactions.reverse', $result->transactionId));

    $response->assertRedirect(route('transactions.show', $result->transactionId));
    $response->assertSessionHas('success');

    $wallet->refresh();
    expect((float) $wallet->balance)->toBe(0.0);
});

test('rejeita reversão de transação já revertida', function () {
    $user = User::factory()->create();
    WalletModel::create(['user_id' => $user->id, 'balance' => '0.00']);

    $depositUseCase = app(DepositUseCase::class);
    $result = $depositUseCase->execute(new DepositInputDTO(
        userId: $user->id,
        amount: '100.00',
    ));

    $this->actingAs($user)
        ->post(route('transactions.reverse', $result->transactionId));

    $response = $this->actingAs($user)
        ->post(route('transactions.reverse', $result->transactionId));

    $response->assertRedirect(route('transactions.show', $result->transactionId));
    $response->assertSessionHasErrors(['reversal']);
});

test('rejeita reversão de transação inexistente', function () {
    $user = User::factory()->create();
    $fakeId = \Ramsey\Uuid\Uuid::uuid4()->toString();

    $response = $this->actingAs($user)
        ->post(route('transactions.reverse', $fakeId));

    $response->assertRedirect(route('transactions.show', $fakeId));
    $response->assertSessionHasErrors(['reversal']);
});

test('saldo é restaurado após reversão de depósito', function () {
    $user = User::factory()->create();
    WalletModel::create(['user_id' => $user->id, 'balance' => '0.00']);

    $depositUseCase = app(DepositUseCase::class);

    $depositUseCase->execute(new DepositInputDTO(
        userId: $user->id,
        amount: '50.00',
    ));

    $result = $depositUseCase->execute(new DepositInputDTO(
        userId: $user->id,
        amount: '100.00',
    ));

    $wallet = WalletModel::where('user_id', $user->id)->first();
    expect((float) $wallet->balance)->toBe(150.0);

    $this->actingAs($user)
        ->post(route('transactions.reverse', $result->transactionId));

    $wallet->refresh();
    expect((float) $wallet->balance)->toBe(50.0);
});
