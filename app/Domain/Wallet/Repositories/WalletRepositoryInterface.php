<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Repositories;

use App\Domain\User\ValueObjects\UserId;
use App\Domain\Wallet\Entities\Wallet;
use App\Domain\Wallet\ValueObjects\WalletId;

/**
 * Contrato para operações de persistência de Wallet.
 *
 * As implementações devem suportar bloqueio pessimista para atualizações
 * de saldo concorrentes via o método findByIdForUpdate.
 */
interface WalletRepositoryInterface
{
    public function findById(WalletId $id): ?Wallet;

    public function findByUserId(UserId $userId): ?Wallet;

    /**
     * Busca uma carteira pelo ID com bloqueio pessimista (SELECT FOR UPDATE).
     *
     * Deve ser chamado dentro de uma transação de banco de dados.
     *
     * @param WalletId $id ID da carteira
     * @return Wallet|null
     */
    public function findByIdForUpdate(WalletId $id): ?Wallet;

    /**
     * Busca uma carteira pelo ID do usuário com bloqueio pessimista.
     *
     * @param UserId $userId ID do usuário
     * @return Wallet|null
     */
    public function findByUserIdForUpdate(UserId $userId): ?Wallet;

    public function save(Wallet $wallet): void;
}
