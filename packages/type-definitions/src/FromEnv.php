<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use Attribute;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableInterface;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableValidatorInterface;
use IfCastle\TypeDefinitions\NativeSerialization\AttributeNameInterface;

#[Attribute(Attribute::TARGET_PARAMETER)]
/**
 * ## Attribute FromEnv.
 *
 * Indicates that the service parameter should be taken from the environment.
 * In this case, the parameter name must match the name of the environment key-value.
 */
readonly class FromEnv implements ArraySerializableInterface, AttributeNameInterface
{
    #[\Override]
    public function toArray(?ArraySerializableValidatorInterface $validator = null): array
    {
        return [$this->key, $this->factory, $this->fromRequestEnv];
    }

    #[\Override]
    public function getAttributeName(): string
    {
        return self::class;
    }

    #[\Override]
    public static function fromArray(array $array, ?ArraySerializableValidatorInterface $validator = null): static
    {
        /* @phpstan-ignore-next-line */
        return new self(...$array);
    }

    public function __construct(
        public ?string $key         = null,
        public ?string $factory     = null,
        public bool $fromRequestEnv = false
    ) {}
}
