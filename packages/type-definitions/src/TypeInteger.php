<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DefinitionIsNotValid;

class TypeInteger extends DefinitionAbstract implements NumberMutableInterface, StringableInterface
{
    protected int|null $minimum     = null;

    protected int|null $maximum     = null;

    public function __construct(string $name, bool $isRequired = true, bool $isNullable = false)
    {
        parent::__construct($name, TypesEnum::INTEGER->value, $isRequired, $isNullable);
    }

    #[\Override]
    public function isBinary(): bool
    {
        return false;
    }

    #[\Override]
    public function getMaxLength(): int|null
    {
        return 11;
    }

    #[\Override]
    public function getMinLength(): int|null
    {
        return 0;
    }

    #[\Override]
    public function getPattern(): string|null
    {
        return '-?\d+';
    }

    #[\Override]
    public function getUriPattern(): string|null
    {
        return $this->getPattern();
    }

    #[\Override]
    public function getEcmaPattern(): string|null
    {
        return '-?\\d+';
    }

    #[\Override]
    public function isScalar(): bool
    {
        return true;
    }

    #[\Override]
    public function isUnsigned(): bool
    {
        return $this->minimum !== null && $this->minimum >= 0;
    }

    #[\Override]
    public function isNonZero(): bool
    {
        return $this->minimum !== null && $this->minimum > 0;
    }

    #[\Override]
    public function getMinimum(): int|float|null
    {
        return $this->minimum;
    }

    #[\Override]
    public function getMaximum(): int|float|null
    {
        return $this->maximum;
    }

    #[\Override]
    public function setMinimum(int|float $minimum): static
    {
        $this->minimum              = (int) $minimum;
        return $this;
    }

    #[\Override]
    public function setMaximum(int|float $maximum): static
    {
        $this->maximum              = (int) $maximum;
        return $this;
    }

    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        if (!\is_numeric($value) || \str_contains(\strtolower($value), 'e')) {
            return false;
        }

        if ($this->minimum !== null && $value < $this->minimum) {
            return false;
        }

        if ($this->maximum !== null && $value > $this->maximum) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function decode(array|int|float|string|bool $data): mixed
    {
        if (\is_numeric($data)) {
            return (int) $data;
        }

        throw new DefinitionIsNotValid($this, 'Type is invalid');
    }

    #[\Override]
    public function encode(mixed $data): mixed
    {
        return $data;
    }

    #[\Override]
    public function canDecodeFromString(): bool
    {
        return true;
    }
}
