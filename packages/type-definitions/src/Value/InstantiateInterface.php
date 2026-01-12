<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Value;

use IfCastle\TypeDefinitions\DefinitionInterface;

interface InstantiateInterface
{
    public static function instantiate(mixed $value, ?DefinitionInterface $definition = null): static;
}
