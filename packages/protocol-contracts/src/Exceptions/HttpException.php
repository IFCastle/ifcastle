<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Exceptions;

use IfCastle\Exceptions\BaseExceptionInterface;
use IfCastle\Exceptions\RuntimeException;

class HttpException extends RuntimeException implements HttpErrorInterface
{
    protected array $tags           = ['request', 'protocol', 'http'];

    public function __construct(
        BaseExceptionInterface|\Throwable|array|string $exception,
        int                                            $code        = 500,
        ?\Throwable                                     $previous    = null
    ) {
        parent::__construct($exception, $code, $previous);
    }

    #[\Override]
    public function getStatusCode(): int
    {
        return $this->getCode();
    }

    #[\Override]
    public function getReasonPhrase(): string|null
    {
        return $this->data['reasonPhrase'] ?? null;
    }
}
