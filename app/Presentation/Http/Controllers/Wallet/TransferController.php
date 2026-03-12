<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Wallet;

use App\Application\UseCases\Transfer\TransferInputDTO;
use App\Application\UseCases\Transfer\TransferUseCase;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Presentation\Http\Requests\TransferRequest;
use App\Presentation\Http\Traits\FormatsMoney;
use App\Shared\Exceptions\DomainException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller responsável pelas operações de transferência entre carteiras.
 */
class TransferController
{
    use FormatsMoney;

    /**
     * @param TransferUseCase         $transferUseCase Caso de uso para realizar transferências
     * @param UserRepositoryInterface $userRepository  Repositório de usuários para busca por e-mail
     */
    public function __construct(
        private readonly TransferUseCase $transferUseCase,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Exibe o formulário de transferência.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('wallet/Transfer');
    }

    /**
     * Processa a transferência para outro usuário.
     *
     * @param TransferRequest $request Requisição validada com e-mail do destinatário e valor
     * @return RedirectResponse
     */
    public function store(TransferRequest $request): RedirectResponse
    {
        try {
            /** @var string $senderId */
            $senderId = $request->user()->id;
            $email = $request->validated('email');

            $receiver = $this->userRepository->findByEmail(new Email($email));

            if ($receiver === null) {
                throw new UserNotFoundException($email);
            }

            $input = new TransferInputDTO(
                senderUserId: $senderId,
                receiverUserId: (string) $receiver->id(),
                amount: (string) $request->validated('amount'),
            );

            $this->transferUseCase->execute($input);

            return redirect()
                ->route('dashboard')
                ->with('success', "Transferência de {$this->formatMoney($request->validated('amount'))} para {$email} realizada com sucesso!");
        } catch (DomainException $e) {
            return redirect()
                ->back()
                ->withErrors(['amount' => $e->getMessage()]);
        }
    }
}
