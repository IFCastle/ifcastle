<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Reader\Exceptions;

use IfCastle\TypeDefinitions\Resolver\TypeContextInterface;

class TypeUndefined extends ReaderException
{
    protected string $template      = 'Type {source} is not defined. Mixed types cannot be used in type definitions.';

    public function __construct(string $source = '', ?TypeContextInterface $typeContext = null)
    {
        if ($typeContext instanceof TypeContextInterface) {
            /* @phpstan-ignore-next-line */
            $sourceString           = match (true) {
                $typeContext->isReturnType()     => 'returnType',
                $typeContext->isProperty()       => 'property',
                $typeContext->isParameter()      => 'parameter'
            };

            if ($source !== '') {
                $sourceString       = ' "' . $source . '" ';
            }

            if ($typeContext->getClassName() !== null) {
                $sourceString       = ' of class ' . $typeContext->getClassName() . ' method ';
            }

            if ($typeContext->getFunctionName() !== null) {
                $sourceString       = $typeContext->getFunctionName();
            }

            $source                 = $sourceString;
        }

        parent::__construct(['source' => $source]);
    }
}
