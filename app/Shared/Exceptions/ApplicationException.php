<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

use RuntimeException;

/**
 * Exceção base para erros na camada de aplicação.
 *
 * Exceções de aplicação representam erros que ocorrem na camada de aplicação,
 * como falhas de autorização ou erros de validação de entrada.
 */
abstract class ApplicationException extends RuntimeException
{
    /**
     * @param string $message Descrição do erro legível por humanos
     * @param string $errorCode Código de erro legível por máquina
     * @param int $httpStatusCode Código HTTP sugerido
     */
    public function __construct(
        string $message,
        private readonly string $errorCode,
        private readonly int $httpStatusCode = 400,
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
