<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Wallet;

use App\Application\UseCases\ReverseTransaction\ReverseTransactionInputDTO;
use App\Application\UseCases\ReverseTransaction\ReverseTransactionUseCase;
use App\Shared\Exceptions\DomainException;
use Illuminate\Http\RedirectResponse;

/**
 * Controller responsável pela reversão de transações.
 */
class ReversalController
{
    /**
     * @param ReverseTransactionUseCase $reverseTransactionUseCase Caso de uso para reversão de transações
     */
    public function __construct(
        private readonly ReverseTransactionUseCase $reverseTransactionUseCase,
    ) {}

    /**
     * Processa a reversão de uma transação.
     *
     * @param string $transactionId Identificador da transação a ser revertida
     * @return RedirectResponse
     */
    public function store(string $transactionId): RedirectResponse
    {
        try {
            $input = new ReverseTransactionInputDTO(
                transactionId: $transactionId,
            );

            $this->reverseTransactionUseCase->execute($input);

            return redirect()
                ->route('transactions.show', $transactionId)
                ->with('success', 'Transação revertida com sucesso!');
        } catch (DomainException $e) {
            return redirect()
                ->route('transactions.show', $transactionId)
                ->withErrors(['reversal' => $e->getMessage()]);
        }
    }
}
