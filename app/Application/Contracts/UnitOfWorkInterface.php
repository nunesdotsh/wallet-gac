<?php

declare(strict_types=1);

namespace App\Application\Contracts;

/**
 * Contrato para transações atômicas de banco de dados.
 *
 * Abstrai o mecanismo de transação de banco de dados para que os casos de uso
 * possam garantir atomicidade sem depender de um ORM ou banco específico.
 */
interface UnitOfWorkInterface
{
    /**
     * Executa o callback fornecido dentro de uma transação de banco de dados.
     *
     * Se o callback lançar uma exceção, a transação é revertida.
     * Caso contrário, é confirmada.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function execute(callable $callback): mixed;
}
