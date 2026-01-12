<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Resolver;

use IfCastle\TypeDefinitions\DefinitionMutableInterface;

/**
 * Answers to the questions:
 *
 * * If this is an interface, which DTO class should correspond to it?
 * * And where should the specified data type be taken from? For example, from parameters or from the environment variables (Env).
 */
interface ResolverInterface
{
    public function resolveType(string $typeName, TypeContextInterface $typeContext): DefinitionMutableInterface|null;
}
