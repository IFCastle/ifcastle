<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Exceptions;

final class NotFound extends RequestException
{
    public function __construct(string      $message = 'Not Found',
        string      $type = '',
        string      $detail = '',
        ?string     $instance = null,
        array       $additionalData = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 404, $type, $detail, $instance, [], $additionalData, $previous);
    }

}
