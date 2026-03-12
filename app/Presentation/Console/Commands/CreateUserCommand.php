<?php

declare(strict_types=1);

namespace App\Presentation\Console\Commands;

use App\Application\UseCases\CreateUser\CreateUserInputDTO;
use App\Application\UseCases\CreateUser\CreateUserUseCase;
use App\Shared\Exceptions\DomainException;
use Illuminate\Console\Command;

/**
 * Comando Artisan para criar um novo usuário com uma carteira.
 */
class CreateUserCommand extends Command
{
    protected $signature = 'wallet:create-user
        {--name= : User full name}
        {--email= : User email address}
        {--password= : User password}';

    protected $description = 'Create a new user with an associated wallet';

    public function handle(CreateUserUseCase $useCase): int
    {
        $name = $this->option('name') ?? $this->ask('Name');
        $email = $this->option('email') ?? $this->ask('Email');
        $password = $this->option('password') ?? $this->secret('Password');

        try {
            $output = $useCase->execute(new CreateUserInputDTO($name, $email, $password));

            $this->info('User created successfully!');
            $this->table(
                ['Field', 'Value'],
                [
                    ['User ID', $output->userId],
                    ['Wallet ID', $output->walletId],
                    ['Name', $output->name],
                    ['Email', $output->email],
                ],
            );

            return self::SUCCESS;
        } catch (DomainException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
