<?php

namespace App\Actions\Fortify;

use App\Application\UseCases\UpdatePassword\UpdatePasswordInputDTO;
use App\Application\UseCases\UpdatePassword\UpdatePasswordUseCase;
use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    public function __construct(
        private readonly UpdatePasswordUseCase $updatePasswordUseCase,
    ) {}

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $this->updatePasswordUseCase->execute(new UpdatePasswordInputDTO(
            userId: $user->id,
            newPassword: $input['password'],
        ));
    }
}
