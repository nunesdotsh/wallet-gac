<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Wallet;

use App\Application\UseCases\GetTransactionHistory\GetTransactionHistoryUseCase;
use App\Presentation\Http\Traits\FormatsMoney;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller responsável pela listagem e exibição de transações.
 */
class TransactionController
{
    use FormatsMoney;

    private const int PER_PAGE = 15;

    /**
     * @param GetTransactionHistoryUseCase $getTransactionHistoryUseCase Caso de uso para histórico de transações
     */
    public function __construct(
        private readonly GetTransactionHistoryUseCase $getTransactionHistoryUseCase,
    ) {}

    /**
     * Lista todas as transações do usuário com paginação manual.
     *
     * @param Request $request Requisição HTTP
     * @return Response
     */
    public function index(Request $request): Response
    {
        /** @var string $userId */
        $userId = $request->user()->id;

        $allTransactions = $this->getTransactionHistoryUseCase->execute($userId);

        $page = max(1, (int) $request->query('page', '1'));
        $total = count($allTransactions);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $offset = ($page - 1) * self::PER_PAGE;

        $transactions = array_slice($allTransactions, $offset, self::PER_PAGE);

        return Inertia::render('wallet/Transactions', [
            'transactions' => [
                'data' => array_map(fn ($tx) => $this->formatTransaction($tx), $transactions),
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => self::PER_PAGE,
                'total' => $total,
            ],
        ]);
    }

    /**
     * Exibe os detalhes de uma transação específica.
     *
     * @param Request $request Requisição HTTP
     * @param string  $id      Identificador da transação
     * @return Response
     * @throws NotFoundHttpException Quando a transação não é encontrada ou não pertence ao usuário
     */
    public function show(Request $request, string $id): Response
    {
        /** @var string $userId */
        $userId = $request->user()->id;

        $allTransactions = $this->getTransactionHistoryUseCase->execute($userId);

        $transaction = null;
        foreach ($allTransactions as $tx) {
            if ($tx->transactionId === $id) {
                $transaction = $tx;
                break;
            }
        }

        if ($transaction === null) {
            throw new NotFoundHttpException('Transação não encontrada.');
        }

        return Inertia::render('wallet/TransactionDetail', [
            'transaction' => $this->formatTransaction($transaction),
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
