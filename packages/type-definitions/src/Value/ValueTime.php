<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Value;

use IfCastle\TypeDefinitions\DefinitionMutableInterface;
use IfCastle\TypeDefinitions\TypeTime;

class ValueTime extends ValueContainer
{
    #[\Override]
    public static function definition(): DefinitionMutableInterface
    {
        return (new TypeTime('time'))->setInstantiableClass(static::class);
    }

    public function __construct(int $hour, int $minute, int $second = 0, ?int $microsecond = null, ?int $timezoneOffset = null)
    {
        parent::__construct(
            [
                'hour'              => $hour,
                'minute'            => $minute,
                'second'            => $second,
                'microsecond'       => $microsecond,
                'timezone'          => $timezoneOffset,
            ]
        );
    }

    #[\Override]
    public function containerToString(): string
    {
        // Output format: 00:00:00.000000+00:00
        // or 00:00:00.000000-00:00
        // or 00:00:00.000000
        // or 00:00:00

        $timezone               = $this->value['timezone'] !== null
            ? \sprintf('%+03d:%02d', (int) ($this->value['timezone'] / 3600), (int) (($this->value['timezone'] % 3600) / 60))
            : '';

        if ($this->value['timezone'] !== null && $this->value['timezone'] < 0) {
            $timezone           = '-' . \substr($timezone, 1);
        }

        $microsecond            = $this->value['microsecond'] !== null
            ? \sprintf('.%06d', $this->value['microsecond'])
            : '';

        return \sprintf('%02d:%02d:%02d%s%s', $this->value['hour'], $this->value['minute'], $this->value['second'], $microsecond, $timezone);
    }

    public function getHour(): int
    {
        return $this->value['hour'];
    }

    public function getMinute(): int
    {
        return $this->value['minute'];
    }

    public function getSecond(): int
    {
        return $this->value['second'];
    }

    public function getMicrosecond(): int
    {
        return $this->value['microsecond'];
    }

    public function getTimezoneOffset(): ?int
    {
        return $this->value['timezone'];
    }

    public function getTimeSeconds(): int
    {
        return $this->value['hour'] * 3600 + $this->value['minute'] * 60 + $this->value['second'];
    }

    public function getTimeMicroseconds(): int
    {
        return $this->value['hour'] * 3600 * 1000000 + $this->value['minute'] * 60 * 1000000 + $this->value['second'] * 1000000 + $this->value['microsecond'];
    }
}
