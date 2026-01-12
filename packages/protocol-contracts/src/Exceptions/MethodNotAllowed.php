<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Exceptions;

final class MethodNotAllowed extends RequestException
{
    public function __construct(
        string      $message        = 'Method Not Allowed',
        string      $type           = '',
        string      $detail         = '',
        ?string     $instance       = null,
        array       $additionalData = [],
        ?\Throwable $previous       = null
    ) {
        parent::__construct($message, 405, $type, $detail, $instance, [], $additionalData, $previous);
    }
}
