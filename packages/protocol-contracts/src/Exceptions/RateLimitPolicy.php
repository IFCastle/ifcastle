<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Exceptions;

final readonly class RateLimitPolicy
{
    public function __construct(
        /*
         * The maximum number of requests that the consumer is permitted to make in a given time window.
         */
        public int $limit,
        /*
         * The number of requests remaining in the current rate limit window.
         */
        public int $remaining,
        /*
         * The time at which the current rate limit window resets in UTC epoch seconds.
         */
        public int $reset
    ) {}
}
