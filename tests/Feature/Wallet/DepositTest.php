<?php

declare(strict_types=1);

/**
 * Testes de funcionalidade para operações de depósito.
 *
 * Verifica o formulário de depósito, a validação de valores
 * e a persistência correta do saldo na carteira.
 */

use App\Infrastructure\Persistence\Eloquent\Models\WalletModel;
use App\Models\User;

test('redireciona para login ao acessar formulário de depósito sem autenticação', function () {
    $response = $this->get(route('deposit.create'));

    $response->assertRedirect(route('login'));
});

test('redireciona para login ao submeter depósito sem autenticação', function () {
    $response = $this->post(route('deposit.store'), ['amount' => 50.00]);

    $response->assertRedirect(route('login'));
});

test('exibe formulário de depósito para usuário autenticado', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('deposit.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('wallet/Deposit'));
});

test('realiza depósito com sucesso', function () {
    $user = User::factory()->create();
    WalletModel::create(['user_id' => $user->id, 'balance' => '0.00']);

    $response = $this->actingAs($user)
        ->post(route('deposit.store'), ['amount' => 50.00]);

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('success');

    $wallet = WalletModel::where('user_id', $user->id)->first();
    expect((float) $wallet->balance)->toBe(50.0);
});

test('realiza múltiplos depósitos e acumula saldo', function () {
    $user = User::factory()->create();
    WalletModel::create(['user_id' => $user->id, 'balance' => '0.00']);

    $this->actingAs($user)->post(route('deposit.store'), ['amount' => 100.00]);
    $this->actingAs($user)->post(route('deposit.store'), ['amount' => 50.50]);

    $wallet = WalletModel::where('user_id', $user->id)->first();
    expect((float) $wallet->balance)->toBe(150.50);
});

test('rejeita depósito com valor zero', function () {
    $user = User::factory()->create();
    WalletModel::create(['user_id' => $user->id, 'balance' => '0.00']);

    $response = $this->actingAs($user)
        ->post(route('deposit.store'), ['amount' => 0]);

    $response->assertSessionHasErrors(['amount']);
});

test('rejeita depósito com valor negativo', function () {
    $user = User::factory()->create();
    WalletModel::create(['user_id' => $user->id, 'balance' => '0.00']);

    $response = $this->actingAs($user)
        ->post(route('deposit.store'), ['amount' => -10]);

    $response->assertSessionHasErrors(['amount']);
});

test('rejeita depósito com valor não numérico', function () {
    $user = User::factory()->create();
    WalletModel::create(['user_id' => $user->id, 'balance' => '0.00']);

    $response = $this->actingAs($user)
        ->post(route('deposit.store'), ['amount' => 'abc']);

    $response->assertSessionHasErrors(['amount']);
});

test('rejeita depósito sem informar valor', function () {
    $user = User::factory()->create();
    WalletModel::create(['user_id' => $user->id, 'balance' => '0.00']);

    $response = $this->actingAs($user)
        ->post(route('deposit.store'), []);

    $response->assertSessionHasErrors(['amount']);
});
