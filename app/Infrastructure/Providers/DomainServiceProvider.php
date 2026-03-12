<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Application\Contracts\UnitOfWorkInterface;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentTransactionRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentWalletRepository;
use App\Infrastructure\Persistence\UnitOfWork;
use Illuminate\Support\ServiceProvider;

/**
 * Vincula interfaces de domínio às suas implementações de infraestrutura.
 *
 * É aqui que o Princípio da Inversão de Dependência é aplicado:
 * o domínio define as interfaces e este provider informa ao Laravel
 * quais implementações concretas injetar.
 *
 * Para trocar o banco de dados (ex: de Eloquent/PostgreSQL para Doctrine
 * ou outro armazenamento), apenas este provider precisa ser alterado.
 */
class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(WalletRepositoryInterface::class, EloquentWalletRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, EloquentTransactionRepository::class);
        $this->app->bind(UnitOfWorkInterface::class, UnitOfWork::class);
    }
}
