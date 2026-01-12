<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Exceptions;

final class InternalServerError extends RequestException
{
    public function __construct(
        string      $message        = 'Internal Server Error',
        string      $type           = '',
        string      $detail         = '',
        ?string     $instance       = null,
        array       $additionalData = [],
        ?\Throwable $previous       = null
    ) {
        parent::__construct($message, 500, $type, $detail, $instance, [], $additionalData, $previous);
    }
}
