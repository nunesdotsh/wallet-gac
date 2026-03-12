<?php

declare(strict_types=1);

namespace App\Presentation\Console\Commands;

use App\Application\UseCases\Deposit\DepositInputDTO;
use App\Application\UseCases\Deposit\DepositUseCase;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Shared\Exceptions\DomainException;
use Illuminate\Console\Command;

/**
 * Comando Artisan para depositar dinheiro na carteira de um usuário.
 */
class DepositCommand extends Command
{
    protected $signature = 'wallet:deposit
        {--email= : User email address}
        {--amount= : Amount to deposit (e.g., 100.50)}
        {--description= : Optional description}';

    protected $description = 'Deposit money into a user\'s wallet';

    public function handle(DepositUseCase $useCase, UserRepositoryInterface $userRepo): int
    {
        $email = $this->option('email') ?? $this->ask('User email');
        $amount = $this->option('amount') ?? $this->ask('Amount to deposit');
        $description = $this->option('description');

        try {
            $user = $userRepo->findByEmail(new Email($email));
            if ($user === null) {
                $this->error("User not found: {$email}");

                return self::FAILURE;
            }

            $output = $useCase->execute(new DepositInputDTO(
                userId: $user->id()->value(),
                amount: $amount,
                description: $description,
            ));

            $this->info('Deposit completed successfully!');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Transaction ID', $output->transactionId],
                    ['Amount', "R$ {$output->amount}"],
                    ['Balance Before', "R$ {$output->balanceBefore}"],
                    ['Balance After', "R$ {$output->balanceAfter}"],
                    ['Status', $output->status],
                ],
            );

            return self::SUCCESS;
        } catch (DomainException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
