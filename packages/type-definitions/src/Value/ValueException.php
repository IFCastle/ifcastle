<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Value;

use IfCastle\TypeDefinitions\DefinitionMutableInterface;
use IfCastle\TypeDefinitions\TypeException;

class ValueException extends ValueObject
{
    #[\Override]
    public static function definition(): DefinitionMutableInterface
    {
        return (new TypeException('exception'))->asReference()->asImmutable();
    }
}
