<?php

namespace App\Actions\Fortify;

use App\Application\UseCases\CreateUser\CreateUserInputDTO;
use App\Application\UseCases\CreateUser\CreateUserUseCase;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(
        private readonly CreateUserUseCase $createUserUseCase,
    ) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        $output = $this->createUserUseCase->execute(new CreateUserInputDTO(
            name: $input['name'],
            email: $input['email'],
            password: $input['password'],
        ));

        return User::findOrFail($output->userId);
    }
}
