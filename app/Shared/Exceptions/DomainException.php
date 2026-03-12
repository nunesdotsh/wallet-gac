<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

use RuntimeException;

/**
 * Exceção base para todos os erros de domínio.
 *
 * Exceções de domínio representam violações de regras de negócio que ocorrem
 * na camada de domínio. Elas carregam um código de erro legível por máquina
 * e uma mensagem legível por humanos.
 */
abstract class DomainException extends RuntimeException
{
    /**
     * @param string $message Descrição do erro legível por humanos
     * @param string $errorCode Código de erro legível por máquina (ex: 'INSUFFICIENT_BALANCE')
     * @param int $httpStatusCode Código HTTP sugerido para respostas de API
     */
    public function __construct(
        string $message,
        private readonly string $errorCode,
        private readonly int $httpStatusCode = 422,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    /**
     * @return array{error_code: string, message: string}
     */
    public function toArray(): array
    {
        return [
            'error_code' => $this->errorCode,
            'message' => $this->getMessage(),
        ];
    }
}
