<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\TypeDefinitions\FunctionDescriptorInterface;

interface AccessCheckerInterface
{
    public function checkAccess(
        object                     $serviceObject,
        ServiceDescriptorInterface $serviceDescriptor,
        FunctionDescriptorInterface $methodDescriptor,
        string                     $service,
        string                     $command
    ): void;
}
