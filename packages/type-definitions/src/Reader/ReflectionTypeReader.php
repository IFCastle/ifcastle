<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Reader;

use IfCastle\Exceptions\RecursionLimitExceeded;
use IfCastle\TypeDefinitions\DefinitionAbstract;
use IfCastle\TypeDefinitions\DefinitionMutableInterface;
use IfCastle\TypeDefinitions\Exceptions\DescribeException;
use IfCastle\TypeDefinitions\Reader\Exceptions\TypeResolveNotAllowed;
use IfCastle\TypeDefinitions\Reader\Exceptions\TypeUndefined;
use IfCastle\TypeDefinitions\Resolver\ResolverInterface;
use IfCastle\TypeDefinitions\Resolver\TypeContextInterface;
use IfCastle\TypeDefinitions\TypeAllOf;
use IfCastle\TypeDefinitions\TypeOneOf;
use IfCastle\TypeDefinitions\TypeVoid;

class ReflectionTypeReader
{
    public function __construct(
        protected readonly \ReflectionParameter|\ReflectionProperty|\ReflectionType|null $definition,
        protected readonly TypeContextInterface $typeContext,
        protected readonly ResolverInterface $resolver
    ) {}

    /**
     * @throws TypeResolveNotAllowed
     * @throws TypeUndefined
     * @throws RecursionLimitExceeded
     * @throws DescribeException
     */
    public function generate(): DefinitionMutableInterface|null
    {
        if ($this->definition === null) {

            if ($this->typeContext->isReturnType()) {
                return new TypeVoid('returnType');
            }

            throw new TypeUndefined('', $this->typeContext);
        }

        if ($this->definition instanceof \ReflectionParameter || $this->definition instanceof \ReflectionProperty) {
            $type                   = $this->definition->getType();
        } else {
            $type                   = $this->definition;
        }

        if ($type === null) {
            throw new TypeUndefined($this->definition->getName(), $this->typeContext);
        }

        return $this->handleType($type);
    }

    /**
     * @throws RecursionLimitExceeded
     * @throws DescribeException
     * @throws TypeResolveNotAllowed
     */
    protected function handleType(
        \ReflectionType|\ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType $type,
        int                                                                                   $recursion = 0
    ): DefinitionMutableInterface|null {
        if ($recursion > 32) {
            throw new RecursionLimitExceeded(32);
        }

        if ($type instanceof \ReflectionUnionType) {
            return $this->handleUnionType($type, $recursion + 1);
        } elseif ($type instanceof \ReflectionIntersectionType) {
            return $this->handleIntersectionType($type, $recursion + 1);
        } elseif ($type instanceof \ReflectionNamedType) {
            return $this->handleNamedType($type);
        }

        return null;
    }

    /**
     * @throws TypeResolveNotAllowed
     */
    protected function handleNamedType(\ReflectionNamedType $type): DefinitionMutableInterface|null
    {
        $name                   = $this->getName();

        if ($type->isBuiltin()) {
            return DefinitionAbstract::getDefinitionByNativeTypeName($type->getName(), $name);
        }

        $definition             = $this->resolver->resolveType($type->getName(), $this->typeContext);

        if ($definition === null) {
            return null;
        }

        // Make a type mutable
        $definition             = clone $definition;

        $definition->addAttributes(...$this->typeContext->getAttributes());

        $definition->setIsNullable($type->allowsNull());

        if ($this->definition instanceof \ReflectionParameter || $this->definition instanceof \ReflectionProperty) {
            $definition->setName($this->definition->getName());
        }

        if ($this->definition instanceof \ReflectionParameter) {
            $definition->setIsRequired(false === $this->definition->isDefaultValueAvailable());

            if ($this->definition->isDefaultValueAvailable()) {
                $definition->setDefaultValue($this->definition->getDefaultValue());
            }
        }

        if ($this->definition instanceof \ReflectionProperty) {
            $definition->setIsRequired(false === $this->definition->isDefault());

            if ($this->definition->hasDefaultValue()) {
                $definition->setDefaultValue($this->definition->getDefaultValue());
            }
        }

        return $definition;
    }

    /**
     * @throws RecursionLimitExceeded
     * @throws DescribeException
     * @throws TypeResolveNotAllowed
     */
    protected function handleUnionType(\ReflectionUnionType $type, int $recursion = 0): DefinitionMutableInterface|null
    {
        $definition             = new TypeOneOf($this->getName());

        foreach ($type->getTypes() as $type) {

            $resolved           = $this->handleType($type, $recursion);

            if ($resolved !== null) {
                $definition->describeCase($resolved);
            }
        }

        if ($definition->getCases() === []) {
            return null;
        }

        return $definition;
    }

    protected function handleIntersectionType(\ReflectionIntersectionType $type, int $recursion = 0): DefinitionMutableInterface|null
    {
        $definition             = new TypeAllOf($this->getName());

        foreach ($type->getTypes() as $type) {

            $resolved           = $this->handleType($type, $recursion);

            if ($resolved !== null) {
                $definition->describeCase($resolved);
            }
        }

        if ($definition->getCases() === []) {
            return null;
        }

        return $definition;
    }

    protected function getName(): string
    {
        if ($this->definition instanceof \ReflectionParameter || $this->definition instanceof \ReflectionProperty) {
            return $this->definition->getName();
        }

        return 'returnType';

    }
}
