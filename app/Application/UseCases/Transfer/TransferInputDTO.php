<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

/**
 * Dados de entrada para operação de transferência.
 */
final readonly class TransferInputDTO
{
    public function __construct(
        public string $senderUserId,
        public string $receiverUserId,
        public string $amount,
        public ?string $description = null,
    ) {}
}
