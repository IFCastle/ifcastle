<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Value;

use IfCastle\TypeDefinitions\DefinitionInterface;
use IfCastle\TypeDefinitions\DefinitionMutableInterface;
use IfCastle\TypeDefinitions\TypeList;
use IfCastle\TypeDefinitions\TypeScalar;

class ValueScalarList extends ValueContainer
{
    #[\Override]
    public static function definition(): DefinitionMutableInterface
    {
        return new TypeList('list', new TypeScalar('item', false, true));
    }

    #[\Override]
    protected function defineDefinition(): DefinitionInterface
    {
        return self::definition();
    }
}
