<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Resolver;

use IfCastle\TypeDefinitions\DefinitionMutableInterface;
use IfCastle\TypeDefinitions\TypeContainer;

/**
 * ## ContainerTypeResolver.
 *
 * Converts the interface into a `TypeContainer` descriptor, which contains the name of the class to be transformed.
 */
class ContainerTypeResolver extends ExplicitTypeResolver
{
    #[\Override]
    public function resolveType(string $typeName, TypeContextInterface $typeContext): DefinitionMutableInterface|null
    {
        $definition                 = parent::resolveType($typeName, $typeContext);

        if ($definition === null && \interface_exists($typeName)) {
            return new TypeContainer('interface', $typeName);
        }

        return $definition;
    }
}
