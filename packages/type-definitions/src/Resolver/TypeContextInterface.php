<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Resolver;

interface TypeContextInterface
{
    public function getClassName(): string|null;

    public function getFunctionName(): string|null;

    public function getParameterName(): string|null;

    public function getPropertyName(): string|null;

    /**
     * @return array<string, object>
     */
    public function getAttributes(): array;

    public function getAttribute(string $attribute): mixed;

    public function hasAttribute(string $attribute): bool;

    public function isReturnType(): bool;

    public function isParameter(): bool;

    public function isProperty(): bool;
}
