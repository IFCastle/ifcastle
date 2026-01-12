<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DecodingException;
use IfCastle\TypeDefinitions\Exceptions\EncodingException;

class TypeDate extends DefinitionAbstract implements StringableInterface
{
    protected string|null $pattern      = '\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2]\d|3[0-1])';

    protected string|null $ecmaPattern  = '[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])';

    protected bool $dateAsImmutable     = true;

    public function __construct(string $name, bool $isRequired = true, bool $isNullable = false)
    {
        parent::__construct($name, TypesEnum::DATE->value, $isRequired, $isNullable);
    }

    public function dateAsImmutable(): static
    {
        $this->dateAsImmutable          = true;

        return $this;
    }

    public function dateAsMutable(): static
    {
        $this->dateAsImmutable          = false;

        return $this;
    }

    #[\Override]
    public function isBinary(): bool
    {
        return false;
    }

    #[\Override]
    public function getMaxLength(): int|null
    {
        return 10;
    }

    #[\Override]
    public function getMinLength(): int|null
    {
        return 10;
    }

    #[\Override]
    public function getPattern(): string|null
    {
        return $this->pattern;
    }

    #[\Override]
    public function getUriPattern(): string|null
    {
        return $this->getPattern();
    }

    #[\Override]
    public function getEcmaPattern(): string|null
    {
        return $this->ecmaPattern;
    }

    #[\Override]
    public function isScalar(): bool
    {
        return true;
    }

    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        if ($value instanceof \DateTime || $value instanceof \DateTimeImmutable) {
            return true;
        }

        return false !== \DateTime::createFromFormat('Y-m-d', $value);
    }

    #[\Override]
    public function encode(mixed $data): mixed
    {
        if ($data instanceof \DateTimeImmutable || $data instanceof \DateTime) {
            return $data->format('Y-m-d');
        }

        throw new EncodingException($this, 'Invalid date format. Expected DateTime or DateTimeImmutable', ['data' => $data]);
    }

    #[\Override]
    public function decode(array|int|float|string|bool|\DateTimeImmutable|\DateTime $data): mixed
    {
        if (\is_string($data)) {
            $data                  = \DateTimeImmutable::createFromFormat('Y-m-d', $data);
        }

        if ($this->dateAsImmutable && $data instanceof \DateTimeImmutable) {

            return $data;
        }

        if ($data instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($data);
        }

        throw new DecodingException($this, 'Invalid date format.', ['data' => $data]);
    }

    #[\Override]
    public function canDecodeFromString(): bool
    {
        return true;
    }
}
