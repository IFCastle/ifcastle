<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\DI\AttributesToDescriptors;
use IfCastle\DI\Binding;
use IfCastle\DI\InjectableInterface;
use IfCastle\TypeDefinitions\FunctionDescriptorInterface;
use IfCastle\TypeDefinitions\PhpdocDescriptionParser;
use IfCastle\TypeDefinitions\Reader\Exceptions\TypeUnresolved;
use IfCastle\TypeDefinitions\Reader\ReflectionFunctionReader;
use IfCastle\TypeDefinitions\Resolver\ResolverInterface;

final class ServiceDescriptorBuilderByReflection implements ServiceDescriptorBuilderInterface
{
    /**
     * @throws \ReflectionException
     * @throws TypeUnresolved
     */
    #[\Override]
    public function buildServiceDescriptor(
        object|string $service,
        string $serviceName,
        ResolverInterface $resolver,
        bool $isActive = true,
        array $config = [],
        bool $useOnlyServiceMethods = true,
        bool $bindWithFirstInterface = false,
        bool $bindWithAllInterfaces = false
    ): ServiceDescriptorInterface {
        $reflectionClass            = new \ReflectionClass($service);
        $attributes                 = $this->buildAttributes($reflectionClass->getAttributes());
        $methods                    = $this->buildMethods($reflectionClass, $resolver, $useOnlyServiceMethods);
        $bindings                   = $this->makeBindings($reflectionClass, $bindWithFirstInterface, $bindWithAllInterfaces);
        $useConstructor             = $this->resolveUseConstructor($reflectionClass);
        [$include, $exclude]        = $this->extractTags($reflectionClass);

        return new ServiceDescriptor(
            $serviceName,
            $reflectionClass->getName(),
            $methods,
            $isActive,
            $config,
            $useConstructor,
            $bindings,
            $this->buildDependencyDescriptors($reflectionClass),
            $attributes,
            $include,
            $exclude,
            '',
            $this->extractDescription($reflectionClass)
        );
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     *
     * @return string[]
     */
    protected function makeBindings(
        \ReflectionClass $reflectionClass,
        bool             $bindWithFirstInterface    = false,
        bool             $bindWithAllInterfaces     = false
    ): array {
        $bindings                   = [];

        $wasAttributeUsed           = false;

        foreach ($reflectionClass->getAttributes(Binding::class) as $binding) {
            $wasAttributeUsed       = true;
            $bindings               = \array_merge($bindings, $binding->newInstance()->interfaces);
        }

        if ($wasAttributeUsed) {
            return $bindings;
        }

        if ($bindWithFirstInterface) {
            $interfaces             = $reflectionClass->getInterfaceNames();

            if (\count($interfaces) > 0) {
                $bindings           = [$interfaces[0]];
            }
        } elseif ($bindWithAllInterfaces) {
            $bindings[]             = $reflectionClass->getInterfaceNames();
        }

        return $bindings;
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     * @return array<string, FunctionDescriptorInterface>
     * @throws TypeUnresolved
     */
    protected function buildMethods(\ReflectionClass $reflectionClass, ResolverInterface $resolver, bool $useOnlyServiceMethods): array
    {
        $functionReader             = new ReflectionFunctionReader($resolver);
        $methods                    = [];

        foreach ($this->fetchClassMethods($reflectionClass, $useOnlyServiceMethods) as $method) {
            $methods[$method->getName()] = $functionReader->extractMethodDescriptor($method, $method->getName());
        }

        return $methods;
    }

    /**
     * @param    \ReflectionAttribute<object>[]    $reflectionAttributes
     * @return   object[]
     */
    protected function buildAttributes(array $reflectionAttributes): array
    {
        $result                     = [];

        foreach ($reflectionAttributes as $reflectionAttribute) {
            $result[]               = $reflectionAttribute->newInstance();
        }

        return $result;
    }

    /**
     * @param \ReflectionClass<object> $class
     * @param array<\ReflectionMethod> $methodReflections
     *
     * @return array<\ReflectionMethod>
     */
    protected function fetchClassMethods(\ReflectionClass $class, bool $useOnlyServiceMethods, array $methodReflections = []): array
    {
        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {

            if ($method->isStatic()) {
                continue;
            }

            if ($method->isAbstract()) {
                continue;
            }

            //
            // We use only those methods that are explicitly marked as service methods
            //
            if ($useOnlyServiceMethods && empty($method->getAttributes(
                AsServiceMethod::class, \ReflectionAttribute::IS_INSTANCEOF
            ))) {
                continue;
            }

            if (false === \array_key_exists($method->getName(), $methodReflections)) {
                $methodReflections[$method->getName()] = $method;
            }
        }

        if ($class->getParentClass() !== false) {
            return $this->fetchClassMethods($class->getParentClass(), $useOnlyServiceMethods, $methodReflections);
        }

        return $methodReflections;
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    protected function resolveUseConstructor(\ReflectionClass $reflectionClass): bool
    {
        return false === \in_array(InjectableInterface::class, $reflectionClass->getInterfaceNames(), true);
    }

    /**
     * @param \ReflectionClass<object> $reflection
     * @return array{0: array<string>, 1: array<string>}
     */
    protected function extractTags(\ReflectionClass $reflection): array
    {
        $include                    = [];
        $exclude                    = [];

        foreach ($reflection->getAttributes(ServiceTags::class) as $tags) {
            /* @var \ReflectionAttribute<ServiceTags> $tags */
            $include                = \array_merge($include, $tags->newInstance()->tags);
        }

        foreach ($reflection->getAttributes(ServiceTagsExclude::class) as $tags) {
            /* @var \ReflectionAttribute<ServiceTagsExclude> $tags */
            $exclude                = \array_merge($exclude, $tags->newInstance()->tags);
        }

        return [$include, $exclude];
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     *
     * @return array<string, \IfCastle\DI\DescriptorInterface>
     * @throws \ReflectionException
     */
    protected function buildDependencyDescriptors(\ReflectionClass $reflectionClass): array
    {
        return AttributesToDescriptors::readDescriptors($reflectionClass);
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    protected function extractDescription(\ReflectionClass $reflectionClass): string
    {
        $comment                    = $reflectionClass->getDocComment();

        if ($comment === false) {
            return '';
        }

        return \implode('\n', PhpdocDescriptionParser::getDescription($comment));
    }
}
