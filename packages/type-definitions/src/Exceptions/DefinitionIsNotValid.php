<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Exceptions;

use IfCastle\Exceptions\ClientException;
use IfCastle\TypeDefinitions\DefinitionInterface;

class DefinitionIsNotValid extends ClientException
{
    protected array $tags           = ['definition'];

    public function __construct(DefinitionInterface $definition, string $message = '', array $debugData = [])
    {
        $debugData['class']         = $definition::class;
        $debugData                  += $definition->toArray();

        parent::__construct(
            'Definition {definition} of {type} is not valid',
            ['message' => $message, 'definition' => $definition->getName(), 'type' => $definition->getTypeName()],
            $debugData
        );
    }
}
