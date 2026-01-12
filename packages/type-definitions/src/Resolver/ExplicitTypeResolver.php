<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Resolver;

use IfCastle\TypeDefinitions\DefinitionMutableInterface;
use IfCastle\TypeDefinitions\DefinitionStaticAwareInterface;
use IfCastle\TypeDefinitions\Type;

/**
 * ## ExplicitTypeResolver.
 *
 * Extracts the type descriptor only if it is explicitly defined:
 *
 * * There is a `Type` attribute.
 * * If it is a class, the `DefinitionStaticAwareInterface` is implemented.
 *
 */
class ExplicitTypeResolver implements ResolverInterface
{
    /**
     * @throws \ReflectionException
     */
    #[\Override]
    public function resolveType(string $typeName, TypeContextInterface $typeContext): DefinitionMutableInterface|null
    {
        $type                       = $typeContext->getAttribute(Type::class);

        if ($type instanceof Type) {
            return $type->definition;
        }

        if (false === \interface_exists($typeName) && \is_subclass_of($typeName, DefinitionStaticAwareInterface::class)) {
            return $typeName::definition();
        }

        // Try to find the Type attribute in the class
        $reflection             = new \ReflectionClass($typeName);
        $attributes             = $reflection->getAttributes(Type::class);

        if ($attributes !== []) {
            return $attributes[0]->newInstance()->definition;
        }

        return null;
    }
}
