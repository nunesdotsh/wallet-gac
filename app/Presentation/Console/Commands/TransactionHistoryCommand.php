<?php

declare(strict_types=1);

namespace App\Presentation\Console\Commands;

use App\Application\UseCases\GetTransactionHistory\GetTransactionHistoryUseCase;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Shared\Exceptions\DomainException;
use Illuminate\Console\Command;

/**
 * Comando Artisan para visualizar o histórico de transações de um usuário.
 */
class TransactionHistoryCommand extends Command
{
    protected $signature = 'wallet:history
        {--email= : User email address}';

    protected $description = 'View transaction history for a user';

    public function handle(GetTransactionHistoryUseCase $useCase, UserRepositoryInterface $userRepo): int
    {
        $email = $this->option('email') ?? $this->ask('User email');

        try {
            $user = $userRepo->findByEmail(new Email($email));
            if ($user === null) {
                $this->error("User not found: {$email}");

                return self::FAILURE;
            }

            $transactions = $useCase->execute($user->id()->value());

            if (empty($transactions)) {
                $this->info("No transactions found for {$email}.");

                return self::SUCCESS;
            }

            $this->info("Transaction history for {$email}:");
            $this->table(
                ['ID', 'Type', 'Amount', 'Before', 'After', 'Status', 'Description', 'Date'],
                array_map(fn ($tx) => [
                    substr($tx->transactionId, 0, 8) . '...',
                    $tx->type,
                    "R$ {$tx->amount}",
                    "R$ {$tx->balanceBefore}",
                    "R$ {$tx->balanceAfter}",
                    $tx->status,
                    $tx->description ?? '-',
                    $tx->createdAt,
                ], $transactions),
            );

            return self::SUCCESS;
        } catch (DomainException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
