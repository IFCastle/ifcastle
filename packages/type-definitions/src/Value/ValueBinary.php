<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Value;

use IfCastle\TypeDefinitions\DefinitionMutableInterface;
use IfCastle\TypeDefinitions\TypeBinary;

class ValueBinary extends ValueString
{
    #[\Override]
    public static function definition(): DefinitionMutableInterface
    {
        return new TypeBinary('binary');
    }
}
