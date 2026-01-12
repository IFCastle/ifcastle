<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DecodingException;
use IfCastle\TypeDefinitions\Exceptions\EncodingException;
use IfCastle\TypeDefinitions\Value\ValueTime;

/**
 * Time definition, like string, but with a specific format.
 * 00:00 or 00:00:00 or 00:00:00.000 or 00:00:00.000000 or 00:00:00.000000+00:00.
 */
class TypeTime extends DefinitionAbstract implements StringableInterface
{
    public function __construct(string $name, bool $isRequired = true, bool $isNullable = false)
    {
        parent::__construct($name, TypesEnum::TIME->value, $isRequired, $isNullable);
    }

    #[\Override]
    public function isBinary(): bool
    {
        return false;
    }

    #[\Override]
    public function getMaxLength(): int|null
    {
        return 15;
    }

    #[\Override]
    public function getMinLength(): int|null
    {
        return 5;
    }

    #[\Override]
    public function getPattern(): string|null
    {
        return '([0-9]{2}):([0-9]{2})(:([0-9]{2})(\.([0-9]{3,6})([+-][0-9]{2}:[0-9]{2})?)?)?';
    }

    #[\Override]
    public function getUriPattern(): string|null
    {
        return $this->getPattern();
    }

    #[\Override]
    public function getEcmaPattern(): string|null
    {
        return '([0-9]{2}):([0-9]{2})(:([0-9]{2})(\.([0-9]{3,6})([+-][0-9]{2}:[0-9]{2})?)?)?';
    }

    #[\Override]
    public function isScalar(): bool
    {
        return true;
    }

    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        if (\is_string($value) === false) {
            return false;
        }

        if (!\preg_match('/^' . $this->getPattern() . '$/', $value, $matches)) {
            return false;
        }

        $hour                       = (int) $matches[1];
        $minute                     = (int) $matches[2];
        $second                     = (int) ($matches[4] ?? 0);
        $microsecond                = null;

        if (isset($matches[6])) {
            $microsecond            = (int) $matches[6];
        }

        // Validate hour and minute and second and microsecond
        if ($hour < 0 || $hour > 23
           || $minute < 0 || $minute > 59
           || $second < 0 || $second > 59
           || ($microsecond !== null && ($microsecond < 0 || $microsecond > 999999))
        ) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function encode(mixed $data): mixed
    {
        if ($data instanceof ValueTime) {
            return $data->containerToString();
        }

        throw new EncodingException($this, 'Expected instance of ValueTime', ['data' => $data]);
    }

    /**
     * @throws DecodingException
     */
    #[\Override]
    public function decode(float|array|bool|int|string $data): mixed
    {
        if (false === \is_string($data)) {
            throw new DecodingException(
                $this,
                'Expected string like 00:00 or 00:00:00 or 00:00:00.000 or 00:00:00.000000 or 00:00:00.000000+00:00 got non string type',
                ['data' => \get_debug_type($data)]
            );
        }

        if (!\preg_match('/^' . $this->getPattern() . '$/', $data, $matches)) {
            throw new DecodingException(
                $this,
                'Expected string like 00:00 or 00:00:00 or 00:00:00.000 or 00:00:00.000000 or 00:00:00.000000+00:00',
                ['data' => $data]
            );
        }

        $hour                       = (int) $matches[1];
        $minute                     = (int) $matches[2];
        $second                     = (int) ($matches[4] ?? 0);
        $microsecond                = null;
        $timezone                   = $matches[7] ?? null;
        $timezoneOffset             = null;

        if (isset($matches[6])) {
            $microsecond            = (int) $matches[6];
        }

        // Validate hour and minute and second and microsecond
        if ($hour < 0 || $hour > 23) {
            throw new DecodingException($this, 'Expected hour between 0 and 23', ['data' => $data]);
        }

        if ($minute < 0 || $minute > 59) {
            throw new DecodingException($this, 'Expected minute between 0 and 59', ['data' => $data]);
        }

        if ($second < 0 || $second > 59) {
            throw new DecodingException($this, 'Expected second between 0 and 59', ['data' => $data]);
        }

        if ($microsecond !== null && ($microsecond < 0 || $microsecond > 999999)) {
            throw new DecodingException($this, 'Expected microsecond between 0 and 999999', ['data' => $data]);
        }

        // Convert timezone to offset in seconds
        if ($timezone !== null) {
            $timezoneOffset         = (int) \substr($timezone, 1, 2) * 3600 + (int) \substr($timezone, 4, 2) * 60;

            if ($timezone[0] === '-') {
                $timezoneOffset     = -$timezoneOffset;
            }
        }

        return new ValueTime($hour, $minute, $second, $microsecond, $timezoneOffset);
    }

    #[\Override]
    public function canDecodeFromString(): bool
    {
        return true;
    }
}
