<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Exceptions;

use IfCastle\TypeDefinitions\DefinitionMutableInterface;

class DefinitionTypeInvalid extends DefinitionIsNotValid
{
    public function __construct(DefinitionMutableInterface $definition, string $message = 'Type invalid', array $debugData = [])
    {
        parent::__construct($definition, $message, $debugData);
    }

}
