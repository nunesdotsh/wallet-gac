<?php

declare(strict_types=1);

/**
 * Testes de funcionalidade do dashboard da carteira.
 *
 * Verifica a exibição do saldo atual, transações recentes
 * e controle de acesso para usuários autenticados.
 */

use App\Application\UseCases\Deposit\DepositInputDTO;
use App\Application\UseCases\Deposit\DepositUseCase;
use App\Infrastructure\Persistence\Eloquent\Models\WalletModel;
use App\Models\User;

test('redireciona para login se não autenticado', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('exibe dashboard para usuário autenticado', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Dashboard'));
});

test('dashboard mostra dados da wallet após depósito', function () {
    $user = User::factory()->create();

    $depositUseCase = app(DepositUseCase::class);
    $depositUseCase->execute(new DepositInputDTO(
        userId: $user->id,
        amount: '100.00',
    ));

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('wallet')
        ->where('wallet.balance', 100)
    );
});

test('dashboard mostra transações recentes', function () {
    $user = User::factory()->create();

    $depositUseCase = app(DepositUseCase::class);
    $depositUseCase->execute(new DepositInputDTO(
        userId: $user->id,
        amount: '50.00',
    ));
    $depositUseCase->execute(new DepositInputDTO(
        userId: $user->id,
        amount: '75.00',
    ));

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('transactions', 2)
    );
});

test('novo usuário já possui wallet com saldo zero', function () {
    $createUserUseCase = app(\App\Application\UseCases\CreateUser\CreateUserUseCase::class);
    $output = $createUserUseCase->execute(new \App\Application\UseCases\CreateUser\CreateUserInputDTO(
        name: 'Novo Usuário',
        email: 'novo@email.com',
        password: 'senha123',
    ));

    $user = User::findOrFail($output->userId);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('wallet')
        ->where('wallet.balance', 0)
    );
});
