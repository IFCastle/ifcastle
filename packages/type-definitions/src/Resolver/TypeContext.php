<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Resolver;

readonly class TypeContext implements TypeContextInterface
{
    /**
     * @param array<object> $attributes
     */
    public function __construct(
        private string|null $className      = null,
        private string|null $functionName   = null,
        private string|null $parameterName  = null,
        private string|null $propertyName   = null,
        private array $attributes           = [],
        private bool $isReturnType          = false,
        private bool $isParameter           = false,
        private bool $isProperty            = false
    ) {}


    #[\Override]
    public function getClassName(): string|null
    {
        return $this->className;
    }

    #[\Override]
    public function getFunctionName(): string|null
    {
        return $this->functionName;
    }

    #[\Override]
    public function getParameterName(): string|null
    {
        return $this->parameterName;
    }

    #[\Override]
    public function getPropertyName(): string|null
    {
        return $this->propertyName;
    }

    #[\Override]
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    #[\Override]
    public function getAttribute(string $attribute): mixed
    {
        foreach ($this->attributes as $attr) {
            if (\is_subclass_of($attr, $attribute)) {
                return $attr;
            }
        }

        return null;
    }

    #[\Override]
    public function hasAttribute(string $attribute): bool
    {
        foreach ($this->attributes as $attr) {
            if (\is_subclass_of($attr, $attribute)) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function isReturnType(): bool
    {
        return $this->isReturnType;
    }

    #[\Override]
    public function isParameter(): bool
    {
        return $this->isParameter;
    }

    #[\Override]
    public function isProperty(): bool
    {
        return $this->isProperty;
    }
}
