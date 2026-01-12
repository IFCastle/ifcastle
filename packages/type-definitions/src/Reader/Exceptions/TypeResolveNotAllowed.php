<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Reader\Exceptions;

use IfCastle\Exceptions\BaseException;
use IfCastle\TypeDefinitions\Resolver\TypeContextInterface;

class TypeResolveNotAllowed extends BaseException
{
    protected string $template       = 'Resolving type "{type}" from {from} is not allowed';

    public function __construct(string $typeName, TypeContextInterface $typeContext)
    {
        $from                       = '';

        if ($typeContext->getClassName() !== null) {
            $from                   = 'class ' . $typeContext->getClassName();

            if ($typeContext->isProperty()) {
                $from               .= ' property ' . $typeContext->getPropertyName();
            } elseif ($typeContext->isParameter()) {
                $from               .= ' parameter ' . $typeContext->getParameterName();
            } else {
                $from               .= ' return type ';
            }
        } elseif ($typeContext->getFunctionName() !== null) {
            $from                   = 'function ' . $typeContext->getFunctionName();
        }

        parent::__construct([
            'type'                      => $typeName,
            'from'                      => $from,
        ]);
    }
}
