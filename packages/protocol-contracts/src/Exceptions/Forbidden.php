<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Exceptions;

final class Forbidden extends RequestException
{
    public function __construct(string      $message = 'Forbidden',
        string      $type = '',
        string      $detail = '',
        ?string     $instance = null,
        array       $additionalData = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 403, $type, $detail, $instance, [], $additionalData, $previous);
    }
}
