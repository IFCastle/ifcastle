<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Exceptions;

class UnprocessableEntity extends RequestException
{
    public function __construct(
        string      $message        = 'Unprocessable Entity',
        string      $type           = '',
        string      $detail         = '',
        ?string     $instance       = null,
        array       $additionalData = [],
        ?\Throwable $previous       = null
    ) {
        parent::__construct($message, 422, $type, $detail, $instance, [], $additionalData, $previous);
    }
}
