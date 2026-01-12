<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

/**
 * Enum with human-readable simple types.
 */
class TypeScalar extends TypeOneOf implements StringableInterface
{
    public function __construct(string $name, bool $isRequired = true, bool $isNullable = false)
    {
        parent::__construct($name, $isRequired, $isNullable);

        $this->describeCase(new TypeBool('boolean'))
            ->describeCase(new TypeString('string'))
            ->describeCase(new TypeInteger('number'))
            ->describeCase(new TypeFloat('float'));
    }

    #[\Override]
    public function canDecodeFromString(): bool
    {
        return true;
    }

    #[\Override]
    public function isBinary(): bool
    {
        return false;
    }

    #[\Override]
    public function getMaxLength(): int|null
    {
        return null;
    }

    #[\Override]
    public function getMinLength(): int|null
    {
        return null;
    }

    #[\Override]
    public function getPattern(): string|null
    {
        return null;
    }

    #[\Override]
    public function getUriPattern(): string|null
    {
        return $this->getPattern();
    }

    #[\Override]
    public function getEcmaPattern(): string|null
    {
        return null;
    }
}
