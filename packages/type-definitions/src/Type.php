<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use Attribute;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableInterface;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableValidatorInterface;
use IfCastle\TypeDefinitions\NativeSerialization\ArrayTyped;
use IfCastle\TypeDefinitions\NativeSerialization\AttributeNameInterface;

/**
 * Specifies a type descriptor for function parameters, properties, class constants.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT | Attribute::TARGET_CLASS)]
readonly class Type implements AttributeNameInterface, ArraySerializableInterface
{
    public function __construct(public DefinitionMutableInterface $definition) {}

    #[\Override]
    public function toArray(?ArraySerializableValidatorInterface $validator = null): array
    {
        return [ArrayTyped::serialize($this->definition, $validator)];
    }

    #[\Override]
    public static function fromArray(array $array, ?ArraySerializableValidatorInterface $validator = null): static
    {
        /* @phpstan-ignore-next-line */
        return new self(ArrayTyped::unserialize($array, $validator));
    }

    #[\Override]
    public function getAttributeName(): string
    {
        return self::class;
    }
}
