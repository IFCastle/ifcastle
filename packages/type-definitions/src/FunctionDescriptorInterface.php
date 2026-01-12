<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

interface FunctionDescriptorInterface extends DefinitionInterface
{
    public const string SCOPE_PUBLIC = 'public';

    public const string SCOPE_INTERNAL = 'internal';

    public function getFunctionName(): string;

    public function getClassName(): string;

    /**
     * @return DefinitionInterface[]
     */
    public function getArguments(): array;

    public function getReturnType(): DefinitionInterface;

    /**
     * @return DefinitionInterface[]
     */
    public function getPossibleErrors(): array;

    public function getScope(): string;

    public function isInternal(): bool;

    public function isPublic(): bool;

    public function isClass(): bool;

    public function isStatic(): bool;
}
