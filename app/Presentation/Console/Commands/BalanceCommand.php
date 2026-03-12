<?php

declare(strict_types=1);

namespace App\Presentation\Console\Commands;

use App\Application\UseCases\GetBalance\GetBalanceUseCase;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Shared\Exceptions\DomainException;
use Illuminate\Console\Command;

/**
 * Comando Artisan para verificar o saldo da carteira de um usuário.
 */
class BalanceCommand extends Command
{
    protected $signature = 'wallet:balance
        {--email= : User email address}';

    protected $description = 'Check a user\'s wallet balance';

    public function handle(GetBalanceUseCase $useCase, UserRepositoryInterface $userRepo): int
    {
        $email = $this->option('email') ?? $this->ask('User email');

        try {
            $user = $userRepo->findByEmail(new Email($email));
            if ($user === null) {
                $this->error("User not found: {$email}");

                return self::FAILURE;
            }

            $output = $useCase->execute($user->id()->value());

            $this->info("Balance for {$email}:");
            $this->table(
                ['Field', 'Value'],
                [
                    ['Wallet ID', $output->walletId],
                    ['Balance', "R$ {$output->balance}"],
                ],
            );

            return self::SUCCESS;
        } catch (DomainException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
