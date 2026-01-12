<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Exceptions;

class Conflict extends RequestException
{
    public function __construct(
        string      $message = 'Conflict',
        string      $type = '',
        string      $detail = '',
        ?string     $instance = null,
        array       $additionalData = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 409, $type, $detail, $instance, [], $additionalData, $previous);
    }

}
