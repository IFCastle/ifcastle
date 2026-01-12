<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\CodeGenerator;

interface CodeGeneratorInterface
{
    public function generateForClass(string $className, ?string $destinationClass = null): string;

    public function generateForMethodCall(string $className, string $methodName, ?string $destinationClass = null): string;

    public function generateForMethodResult(string $className, string $methodName, ?string $destinationClass = null): string;
}
