<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Exceptions;

final class ServiceUnavailable extends RequestException
{
    public function __construct(
        protected int $retryAfter = 0,
        string      $message        = 'Service Unavailable',
        string      $type           = '',
        string      $detail         = '',
        ?string     $instance       = null,
        array       $additionalData = [],
        ?\Throwable $previous       = null
    ) {

        $additionalData['retryAfter'] = $this->retryAfter;

        parent::__construct($message, 503, $type, $detail, $instance, [], $additionalData, $previous);
    }
}
