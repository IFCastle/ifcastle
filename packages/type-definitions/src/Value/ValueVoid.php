<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Value;

use IfCastle\TypeDefinitions\DefinitionInterface;
use IfCastle\TypeDefinitions\DefinitionMutableInterface;
use IfCastle\TypeDefinitions\TypeNull;

class ValueVoid implements ValueContainerInterface
{
    #[\Override]
    public function containerSerialize(): array|string|bool|int|float|null
    {
        return null;
    }

    #[\Override]
    public function containerToString(): string
    {
        return '';
    }

    #[\Override]
    public static function definition(): DefinitionMutableInterface
    {
        return new TypeNull('void');
    }

    #[\Override]
    public static function instantiate(mixed $value, ?DefinitionInterface $definition = null): static
    {
        /* @phpstan-ignore-next-line */
        return new self();
    }

    #[\Override]
    public function getDefinition(): DefinitionInterface
    {
        return self::definition();
    }

    #[\Override]
    public function getValue(): mixed
    {
        return null;
    }
}
