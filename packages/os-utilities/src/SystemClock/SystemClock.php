<?php

declare(strict_types=1);

namespace IfCastle\OsUtilities\SystemClock;

class SystemClock implements SystemClockInterface
{
    private static int $referenceTime = 0;

    #[\Override]
    public function now(): int
    {
        if (self::$referenceTime === 0) {
            self::$referenceTime = self::calculateReferenceTime(
                \microtime(true),
                \hrtime(true)
            );
        }

        return self::$referenceTime + \hrtime(true);
    }

    #[\Override]
    public function toSeconds(int $nanos): float
    {
        return $nanos / self::NANOS_PER_SECOND;
    }

    /**
     * Calculates the reference time which is later used to calculate the current wall clock time
     * in nanoseconds by adding the current uptime.
     */
    private static function calculateReferenceTime(float $wallClockMicroTime, int $upTime): int
    {
        return ((int) ($wallClockMicroTime * SystemClockInterface::NANOS_PER_SECOND)) - $upTime;
    }
}
