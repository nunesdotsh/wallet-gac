<?php

declare(strict_types=1);

namespace App\Presentation\Console\Commands;

use App\Application\UseCases\Transfer\TransferInputDTO;
use App\Application\UseCases\Transfer\TransferUseCase;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Shared\Exceptions\DomainException;
use Illuminate\Console\Command;

/**
 * Comando Artisan para transferir dinheiro entre usuários.
 */
class TransferCommand extends Command
{
    protected $signature = 'wallet:transfer
        {--from= : Sender email address}
        {--to= : Receiver email address}
        {--amount= : Amount to transfer (e.g., 50.00)}
        {--description= : Optional description}';

    protected $description = 'Transfer money between two users';

    public function handle(TransferUseCase $useCase, UserRepositoryInterface $userRepo): int
    {
        $fromEmail = $this->option('from') ?? $this->ask('Sender email');
        $toEmail = $this->option('to') ?? $this->ask('Receiver email');
        $amount = $this->option('amount') ?? $this->ask('Amount to transfer');
        $description = $this->option('description');

        try {
            $sender = $userRepo->findByEmail(new Email($fromEmail));
            if ($sender === null) {
                $this->error("Sender not found: {$fromEmail}");

                return self::FAILURE;
            }

            $receiver = $userRepo->findByEmail(new Email($toEmail));
            if ($receiver === null) {
                $this->error("Receiver not found: {$toEmail}");

                return self::FAILURE;
            }

            $output = $useCase->execute(new TransferInputDTO(
                senderUserId: $sender->id()->value(),
                receiverUserId: $receiver->id()->value(),
                amount: $amount,
                description: $description,
            ));

            $this->info('Transfer completed successfully!');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Amount', "R$ {$output->amount}"],
                    ['Sender Balance Before', "R$ {$output->senderBalanceBefore}"],
                    ['Sender Balance After', "R$ {$output->senderBalanceAfter}"],
                    ['Receiver Balance Before', "R$ {$output->receiverBalanceBefore}"],
                    ['Receiver Balance After', "R$ {$output->receiverBalanceAfter}"],
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
