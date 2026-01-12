<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\TypeDefinitions\DefinitionInterface;
use IfCastle\TypeDefinitions\FunctionDescriptorInterface;

interface ParameterResolverInterface
{
    /**
     * Resolves the parameters for a given method.
     *
     * @param array<string, mixed> $rawParameters
     *
     */
    public function resolveParameters(DefinitionInterface $parameter, FunctionDescriptorInterface $methodDescriptor, array $rawParameters = []): mixed;
}
