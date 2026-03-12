<?php

declare(strict_types=1);

/**
 * Testes de funcionalidade para operações de transferência.
 *
 * Verifica o formulário de transferência, validações de saldo,
 * destinatário e valor, além da persistência correta dos saldos.
 */

use App\Application\UseCases\Deposit\DepositInputDTO;
use App\Application\UseCases\Deposit\DepositUseCase;
use App\Infrastructure\Persistence\Eloquent\Models\WalletModel;
use App\Models\User;

test('redireciona para login ao acessar formulário de transferência sem autenticação', function () {
    $response = $this->get(route('transfer.create'));

    $response->assertRedirect(route('login'));
});

test('redireciona para login ao submeter transferência sem autenticação', function () {
    $response = $this->post(route('transfer.store'), [
        'email' => 'test@example.com',
        'amount' => 50.00,
    ]);

    $response->assertRedirect(route('login'));
});

test('exibe formulário de transferência para usuário autenticado', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('transfer.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('wallet/Transfer'));
});

test('realiza transferência com sucesso', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();
    WalletModel::create(['user_id' => $sender->id, 'balance' => '0.00']);
    WalletModel::create(['user_id' => $receiver->id, 'balance' => '0.00']);

    $depositUseCase = app(DepositUseCase::class);
    $depositUseCase->execute(new DepositInputDTO(
        userId: $sender->id,
        amount: '200.00',
    ));

    $response = $this->actingAs($sender)
        ->post(route('transfer.store'), [
            'email' => $receiver->email,
            'amount' => 50.00,
        ]);

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('success');

    $senderWallet = WalletModel::where('user_id', $sender->id)->first();
    $receiverWallet = WalletModel::where('user_id', $receiver->id)->first();
    expect((float) $senderWallet->balance)->toBe(150.0);
    expect((float) $receiverWallet->balance)->toBe(50.0);
});

test('rejeita transferência com saldo insuficiente', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();
    WalletModel::create(['user_id' => $sender->id, 'balance' => '0.00']);
    WalletModel::create(['user_id' => $receiver->id, 'balance' => '0.00']);

    $response = $this->actingAs($sender)
        ->post(route('transfer.store'), [
            'email' => $receiver->email,
            'amount' => 100.00,
        ]);

    $response->assertSessionHasErrors(['amount']);
});

test('rejeita transferência para email inexistente', function () {
    $sender = User::factory()->create();
    WalletModel::create(['user_id' => $sender->id, 'balance' => '0.00']);

    $depositUseCase = app(DepositUseCase::class);
    $depositUseCase->execute(new DepositInputDTO(
        userId: $sender->id,
        amount: '200.00',
    ));

    $response = $this->actingAs($sender)
        ->post(route('transfer.store'), [
            'email' => 'inexistente@example.com',
            'amount' => 50.00,
        ]);

    $response->assertSessionHasErrors(['email']);
});

test('rejeita transferência com valor inválido', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    $response = $this->actingAs($sender)
        ->post(route('transfer.store'), [
            'email' => $receiver->email,
            'amount' => -10,
        ]);

    $response->assertSessionHasErrors(['amount']);
});

test('rejeita transferência sem informar valor', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    $response = $this->actingAs($sender)
        ->post(route('transfer.store'), [
            'email' => $receiver->email,
        ]);

    $response->assertSessionHasErrors(['amount']);
});

test('rejeita transferência sem informar email', function () {
    $sender = User::factory()->create();

    $response = $this->actingAs($sender)
        ->post(route('transfer.store'), [
            'amount' => 50.00,
        ]);

    $response->assertSessionHasErrors(['email']);
});

test('rejeita transferência para si mesmo', function () {
    $sender = User::factory()->create();
    WalletModel::create(['user_id' => $sender->id, 'balance' => '0.00']);

    $depositUseCase = app(DepositUseCase::class);
    $depositUseCase->execute(new DepositInputDTO(
        userId: $sender->id,
        amount: '200.00',
    ));

    $response = $this->actingAs($sender)
        ->post(route('transfer.store'), [
            'email' => $sender->email,
            'amount' => 50.00,
        ]);

    $response->assertSessionHasErrors(['amount']);
});
