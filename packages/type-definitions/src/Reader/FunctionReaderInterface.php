<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Reader;

use IfCastle\TypeDefinitions\FunctionDescriptorInterface;

interface FunctionReaderInterface
{
    public function extractFunctionDescriptor(string|\Closure|\ReflectionFunction $function): FunctionDescriptorInterface;

    public function extractMethodDescriptor(string|object $object, string $method): FunctionDescriptorInterface;
}
