<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Exceptions;

final class Unauthorized extends RequestException
{
    public function __construct(
        string      $message        = 'Unauthorized',
        string      $type           = '',
        string      $detail         = '',
        ?string     $instance       = null,
        array       $additionalData = [],
        ?\Throwable $previous       = null
    ) {
        parent::__construct($message, 401, $type, $detail, $instance, [], $additionalData, $previous);
    }
}
