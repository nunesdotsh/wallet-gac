<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Contracts\UnitOfWorkInterface;
use Illuminate\Support\Facades\DB;

/**
 * Implementação de transação de banco de dados utilizando a facade DB do Laravel.
 *
 * Encapsula operações em uma transação de banco de dados, garantindo atomicidade.
 * Reverte em caso de exceção, confirma em caso de sucesso.
 */
final class UnitOfWork implements UnitOfWorkInterface
{
    public function execute(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
