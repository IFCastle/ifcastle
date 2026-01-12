<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

class TypeFunction extends TypeObject implements FunctionDescriptorInterface
{
    protected string $functionName  = '';

    protected DefinitionInterface $returnType;

    /**
     * array of return type errors.
     *
     * @var DefinitionInterface[]
     */
    protected array $possibleErrors = [];

    public function __construct(
        string $name,
        protected string $className           = '',
        protected string $scope               = '',
        protected bool   $isStatic            = false,
        bool   $isRequired          = true,
        bool   $isNullable          = false
    ) {
        parent::__construct(
            $name,
            $isRequired,
            $isNullable
        );

        $this->type                 = 'function';
        $this->functionName         = $name;
    }

    #[\Override]
    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    #[\Override]
    public function getClassName(): string
    {
        return $this->className;
    }

    #[\Override]
    public function getArguments(): array
    {
        return $this->properties;
    }

    #[\Override]
    public function getReturnType(): DefinitionInterface
    {
        return $this->returnType;
    }

    #[\Override]
    public function getPossibleErrors(): array
    {
        return $this->possibleErrors;
    }

    public function describeReturnType(DefinitionInterface $returnType): static
    {
        $this->returnType           = $returnType;

        return $this;
    }

    public function describePossibleErrors(DefinitionInterface ...$errors): static
    {
        $this->possibleErrors       = $errors;

        return $this;
    }

    #[\Override]
    public function getScope(): string
    {
        return $this->scope;
    }

    #[\Override]
    public function isInternal(): bool
    {
        return $this->scope === FunctionDescriptorInterface::SCOPE_INTERNAL;
    }

    #[\Override]
    public function isPublic(): bool
    {
        return $this->scope === FunctionDescriptorInterface::SCOPE_PUBLIC;
    }

    #[\Override]
    public function isClass(): bool
    {
        return $this->className !== '';
    }

    #[\Override]
    public function isStatic(): bool
    {
        return $this->isStatic;
    }
}
