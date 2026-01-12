<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Value;

use IfCastle\TypeDefinitions\DefinitionInterface;
use IfCastle\TypeDefinitions\DefinitionMutableInterface;
use IfCastle\TypeDefinitions\TypeKeyList;
use IfCastle\TypeDefinitions\TypeScalar;

class ValueScalarKeyList extends ValueContainer
{
    #[\Override]
    public static function definition(): DefinitionMutableInterface
    {
        return new TypeKeyList('item', new TypeScalar('item', false, true));
    }


    #[\Override]
    protected function defineDefinition(): DefinitionInterface
    {
        return self::definition();
    }
}
