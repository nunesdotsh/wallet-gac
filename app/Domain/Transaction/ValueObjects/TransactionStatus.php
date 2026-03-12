<?php

declare(strict_types=1);

namespace App\Domain\Transaction\ValueObjects;

/**
 * Representa o status do ciclo de vida de uma transação financeira.
 *
 * COMPLETED: Transação processada com sucesso.
 * REVERSED: Transação estornada (manualmente ou por inconsistência).
 * FAILED: Transação falhou durante o processamento.
 */
enum TransactionStatus: string
{
    case COMPLETED = 'completed';
    case REVERSED = 'reversed';
    case FAILED = 'failed';

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isReversed(): bool
    {
        return $this === self::REVERSED;
    }

    public function canBeReversed(): bool
    {
        return $this === self::COMPLETED;
    }
}
