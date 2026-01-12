<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Value;

use IfCastle\TypeDefinitions\DefinitionMutableInterface;
use IfCastle\TypeDefinitions\TypeTimestamp;

class ValueTimestamp extends ValueNumber
{
    #[\Override]
    public static function definition(): DefinitionMutableInterface
    {
        return new TypeTimestamp('timestamp');
    }
}
