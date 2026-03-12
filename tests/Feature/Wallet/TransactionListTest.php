<?php

declare(strict_types=1);

/**
 * Testes de funcionalidade para listagem e exibição de transações.
 *
 * Verifica a listagem paginada, a exibição de detalhes
 * e o controle de acesso às transações do usuário.
 */

use App\Application\UseCases\Deposit\DepositInputDTO;
use App\Application\UseCases\Deposit\DepositUseCase;
use App\Infrastructure\Persistence\Eloquent\Models\TransactionModel;
use App\Infrastructure\Persistence\Eloquent\Models\WalletModel;
use App\Models\User;

test('redireciona para login ao acessar transações sem autenticação', function () {
    $response = $this->get(route('transactions.index'));

    $response->assertRedirect(route('login'));
});

test('exibe lista vazia para usuário sem transações', function () {
    $user = User::factory()->create();
    WalletModel::create(['user_id' => $user->id, 'balance' => '0.00']);

    $response = $this->actingAs($user)->get(route('transactions.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('wallet/Transactions')
        ->where('transactions.data', [])
        ->where('transactions.total', 0)
    );
});

test('exibe transações após depósito', function () {
    $user = User::factory()->create();
    WalletModel::create(['user_id' => $user->id, 'balance' => '0.00']);

    $depositUseCase = app(DepositUseCase::class);
    $depositUseCase->execute(new DepositInputDTO(
        userId: $user->id,
        amount: '100.00',
    ));

    $response = $this->actingAs($user)->get(route('transactions.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('wallet/Transactions')
        ->where('transactions.total', 1)
        ->has('transactions.data', 1)
    );
});

test('exibe detalhes de uma transação específica', function () {
    $user = User::factory()->create();
    WalletModel::create(['user_id' => $user->id, 'balance' => '0.00']);

    $depositUseCase = app(DepositUseCase::class);
    $result = $depositUseCase->execute(new DepositInputDTO(
        userId: $user->id,
        amount: '100.00',
    ));

    $response = $this->actingAs($user)
        ->get(route('transactions.show', $result->transactionId));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('wallet/TransactionDetail')
        ->has('transaction')
        ->where('transaction.id', $result->transactionId)
        ->where('transaction.amount', 100)
    );
});

test('retorna 404 para transação inexistente', function () {
    $user = User::factory()->create();
    WalletModel::create(['user_id' => $user->id, 'balance' => '0.00']);

    $response = $this->actingAs($user)
        ->get(route('transactions.show', 'id-inexistente'));

    $response->assertNotFound();
});

test('paginação funciona corretamente', function () {
    $user = User::factory()->create();
    WalletModel::create(['user_id' => $user->id, 'balance' => '0.00']);

    $depositUseCase = app(DepositUseCase::class);
    for ($i = 0; $i < 20; $i++) {
        $depositUseCase->execute(new DepositInputDTO(
            userId: $user->id,
            amount: '10.00',
        ));
    }

    $responsePage1 = $this->actingAs($user)->get(route('transactions.index', ['page' => 1]));
    $responsePage1->assertOk();
    $responsePage1->assertInertia(fn ($page) => $page
        ->where('transactions.total', 20)
        ->where('transactions.current_page', 1)
        ->where('transactions.per_page', 15)
        ->has('transactions.data', 15)
    );

    $responsePage2 = $this->actingAs($user)->get(route('transactions.index', ['page' => 2]));
    $responsePage2->assertOk();
    $responsePage2->assertInertia(fn ($page) => $page
        ->where('transactions.current_page', 2)
        ->has('transactions.data', 5)
    );
});

test('redireciona para login ao acessar detalhe de transação sem autenticação', function () {
    $response = $this->get(route('transactions.show', 'qualquer-id'));

    $response->assertRedirect(route('login'));
});
