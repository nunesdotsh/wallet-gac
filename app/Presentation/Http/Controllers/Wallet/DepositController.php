<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Wallet;

use App\Application\UseCases\Deposit\DepositInputDTO;
use App\Application\UseCases\Deposit\DepositUseCase;
use App\Presentation\Http\Requests\DepositRequest;
use App\Presentation\Http\Traits\FormatsMoney;
use App\Shared\Exceptions\DomainException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller responsável pelas operações de depósito na carteira.
 */
class DepositController
{
    use FormatsMoney;

    /**
     * @param DepositUseCase $depositUseCase Caso de uso para realizar depósitos
     */
    public function __construct(
        private readonly DepositUseCase $depositUseCase,
    ) {}

    /**
     * Exibe o formulário de depósito.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('wallet/Deposit');
    }

    /**
     * Processa o depósito na carteira do usuário autenticado.
     *
     * @param DepositRequest $request Requisição validada com o valor do depósito
     * @return RedirectResponse
     */
    public function store(DepositRequest $request): RedirectResponse
    {
        try {
            /** @var string $userId */
            $userId = $request->user()->id;

            $input = new DepositInputDTO(
                userId: $userId,
                amount: (string) $request->validated('amount'),
            );

            $this->depositUseCase->execute($input);

            return redirect()
                ->route('dashboard')
                ->with('success', "Depósito de {$this->formatMoney($request->validated('amount'))} realizado com sucesso!");
        } catch (DomainException $e) {
            return redirect()
                ->back()
                ->withErrors(['amount' => $e->getMessage()]);
        }
    }
}
