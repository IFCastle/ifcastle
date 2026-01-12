<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

/**
 * Enum with human-readable simple types.
 */
class TypeStringNumber extends TypeOneOf
{
    public function __construct(string $name, bool $isRequired = true, bool $isNullable = false)
    {
        parent::__construct($name, $isRequired, $isNullable);

        $this->describeCase(new TypeString('string'))
            ->describeCase(new TypeInteger('integer'))
            ->describeCase(new TypeFloat('float'));
    }

    #[\Override]
    public function canDecodeFromString(): bool
    {
        return true;
    }
}
