<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

class TypeErrorMessage extends TypeObject
{
    public function __construct(string $name, DefinitionInterface ...$parameters)
    {
        parent::__construct($name);

        $this->type                 = 'errorMessage';

        $this->describe((new TypeString('message'))->setDescription('The error message.'));

        if ($parameters !== []) {

            $object                 = new TypeObject('parameters');

            foreach ($parameters as $parameter) {
                $this->describe($parameter);
            }

            $this->describe($object);
        }
    }
}
