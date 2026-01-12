<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\TypeDefinitions\Resolver\ResolverInterface;

interface ServiceDescriptorBuilderInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function buildServiceDescriptor(
        object|string     $service,
        string            $serviceName,
        ResolverInterface $resolver,
        bool              $isActive     = true,
        array             $config       = [],
        bool $useOnlyServiceMethods     = true,
        bool $bindWithFirstInterface    = false,
        bool $bindWithAllInterfaces     = false
    ): ServiceDescriptorInterface;
}
