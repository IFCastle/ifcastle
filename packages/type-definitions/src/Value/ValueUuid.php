<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Value;

use IfCastle\TypeDefinitions\DefinitionMutableInterface;
use IfCastle\TypeDefinitions\TypeUuid;

class ValueUuid extends ValueContainer
{
    #[\Override]
    public static function definition(): DefinitionMutableInterface
    {
        return new TypeUuid('guid');
    }

    public function __construct(string $value)
    {
        parent::__construct($value);
    }
}
