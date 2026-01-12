<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Exceptions;

class TooManyRequests extends RequestException
{
    public function __construct(
        protected ?RateLimitPolicy $rateLimitPolicy = null,
        string      $message        = 'Too Many Requests',
        string      $type           = '',
        string      $detail         = '',
        ?string     $instance       = null,
        array       $additionalData = [],
        ?\Throwable $previous       = null
    ) {
        if ($rateLimitPolicy !== null) {
            $additionalData['rateLimit'] = [
                'limit'             => $rateLimitPolicy->limit,
                'remaining'         => $rateLimitPolicy->remaining,
                'reset'             => $rateLimitPolicy->reset,
            ];
        }

        parent::__construct($message, 429, $type, $detail, $instance, [], $additionalData, $previous);
    }

    public function getRateLimitPolicy(): ?RateLimitPolicy
    {
        return $this->rateLimitPolicy;
    }
}
