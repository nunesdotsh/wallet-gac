<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Wallet;

use App\Application\UseCases\GetBalance\GetBalanceUseCase;
use App\Application\UseCases\GetTransactionHistory\GetTransactionHistoryUseCase;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Presentation\Http\Traits\FormatsMoney;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller responsável pela tela principal (dashboard) da carteira.
 *
 * Exibe o saldo atual e as últimas transações do usuário autenticado.
 */
class DashboardController
{
    use FormatsMoney;

    /**
     * @param GetBalanceUseCase            $getBalanceUseCase            Caso de uso para consulta de saldo
     * @param GetTransactionHistoryUseCase $getTransactionHistoryUseCase Caso de uso para histórico de transações
     */
    public function __construct(
        private readonly GetBalanceUseCase $getBalanceUseCase,
        private readonly GetTransactionHistoryUseCase $getTransactionHistoryUseCase,
    ) {}

    /**
     * Exibe o dashboard com saldo e últimas transações.
     *
     * @param Request $request Requisição HTTP
     * @return Response
     */
    public function index(Request $request): Response
    {
        /** @var string $userId */
        $userId = $request->user()->id;

        $wallet = null;
        $transactions = [];

        try {
            $balance = $this->getBalanceUseCase->execute($userId);
            $wallet = [
                'id' => $balance->walletId,
                'balance' => (float) $balance->balance,
                'formatted_balance' => $this->formatMoney($balance->balance),
            ];

            $allTransactions = $this->getTransactionHistoryUseCase->execute($userId);
            $recentTransactions = array_slice($allTransactions, 0, 10);
            $transactions = array_map(fn ($tx) => $this->formatTransaction($tx), $recentTransactions);
        } catch (WalletNotFoundException) {
            // Usuário ainda não possui carteira
        }

        return Inertia::render('Dashboard', [
            'wallet' => $wallet,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Formata os dados de uma transação para exibição.
     *
     * @param object $tx Item do histórico de transações
     * @return array<string, mixed>
     */
    private function formatTransaction(object $tx): array
    {
        return [
            'id' => $tx->transactionId,
            'type' => $tx->type,
            'status' => $tx->status,
            'amount' => (float) $tx->amount,
            'formatted_amount' => $this->formatMoney($tx->amount),
            'balance_before' => (float) $tx->balanceBefore,
            'balance_after' => (float) $tx->balanceAfter,
            'formatted_balance_before' => $this->formatMoney($tx->balanceBefore),
            'formatted_balance_after' => $this->formatMoney($tx->balanceAfter),
            'counterpart_name' => null,
            'counterpart_email' => null,
            'description' => $tx->description,
            'reversed_at' => $tx->reversedAt,
            'created_at' => $tx->createdAt,
        ];
    }
}
