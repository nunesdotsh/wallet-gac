<?php

namespace App\Http\Controllers\Settings;

use App\Application\UseCases\UpdatePassword\UpdatePasswordInputDTO;
use App\Application\UseCases\UpdatePassword\UpdatePasswordUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    public function __construct(
        private readonly UpdatePasswordUseCase $updatePasswordUseCase,
    ) {}

    /**
     * Show the user's password settings page.
     */
    public function edit(): Response
    {
        return Inertia::render('settings/Password');
    }

    /**
     * Atualiza a senha do usuário.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $this->updatePasswordUseCase->execute(new UpdatePasswordInputDTO(
            userId: $request->user()->id,
            newPassword: $request->password,
        ));

        return back();
    }
}
