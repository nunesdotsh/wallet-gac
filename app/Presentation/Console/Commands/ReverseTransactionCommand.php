<?php

declare(strict_types=1);

namespace App\Presentation\Console\Commands;

use App\Application\UseCases\ReverseTransaction\ReverseTransactionInputDTO;
use App\Application\UseCases\ReverseTransaction\ReverseTransactionUseCase;
use App\Shared\Exceptions\DomainException;
use Illuminate\Console\Command;

/**
 * Comando Artisan para estornar uma transação.
 */
class ReverseTransactionCommand extends Command
{
    protected $signature = 'wallet:reverse
        {--transaction-id= : Transaction ID to reverse}
        {--reason= : Reason for reversal}';

    protected $description = 'Reverse a completed transaction';

    public function handle(ReverseTransactionUseCase $useCase): int
    {
        $transactionId = $this->option('transaction-id') ?? $this->ask('Transaction ID');
        $reason = $this->option('reason') ?? $this->ask('Reason for reversal (optional)', 'User request');

        if (!$this->confirm("Are you sure you want to reverse transaction {$transactionId}?")) {
            $this->info('Reversal cancelled.');

            return self::SUCCESS;
        }

        try {
            $output = $useCase->execute(new ReverseTransactionInputDTO(
                transactionId: $transactionId,
                reason: $reason,
            ));

            $this->info('Transaction reversed successfully!');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Reversal Transaction ID', $output->reversalTransactionId],
                    ['Original Transaction ID', $output->originalTransactionId],
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
