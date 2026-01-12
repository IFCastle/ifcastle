<?php

declare(strict_types=1);

namespace IfCastle\OsUtilities\SystemClock;

interface SystemClockInterface
{
    final public const int MILLIS_PER_SECOND        = 1_000;

    final public const int MICROS_PER_SECOND        = 1_000_000;

    final public const int NANOS_PER_SECOND         = 1_000_000_000;

    final public const int NANOS_PER_MILLISECOND    = 1_000_000;

    final public const int NANOS_PER_MICROSECOND    = 1_000;

    /**
     * Returns the current epoch wall-clock timestamp in nanoseconds.
     */
    public function now(): int;

    public function toSeconds(int $nanos): float;
}
