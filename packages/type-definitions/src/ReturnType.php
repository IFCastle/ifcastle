<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use Attribute;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableInterface;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableValidatorInterface;
use IfCastle\TypeDefinitions\NativeSerialization\ArrayTyped;
use IfCastle\TypeDefinitions\NativeSerialization\AttributeNameInterface;

/**
 * Allows you to specify the return type for functions.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
readonly class ReturnType implements AttributeNameInterface, ArraySerializableInterface
{
    /**
     * Return type specifier.
     */
    public function __construct(public DefinitionInterface $definition) {}

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
