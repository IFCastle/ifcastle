<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\DI\ConfigurationProviderInterface;
use IfCastle\DI\ConstructibleInterface;
use IfCastle\DI\DescriptorInterface;
use IfCastle\ServiceManager\Exceptions\MethodNotFound;
use IfCastle\TypeDefinitions\AttributesTrait;
use IfCastle\TypeDefinitions\FunctionDescriptorInterface;

class ServiceDescriptor implements ServiceDescriptorInterface, ConstructibleInterface, ConfigurationProviderInterface
{
    use AttributesTrait;

    /**
     * @param array<string, FunctionDescriptorInterface> $methods
     * @param array<string, object>                      $attributes
     * @param array<string>                              $includeTags
     * @param array<string>                              $excludeTags
     * @param DescriptorInterface[]                      $dependencies
     * @param array<string, mixed>                       $bindings
     * @param array<string, mixed>                       $config
     */
    public function __construct(
        protected string $serviceName,
        protected string $className,
        protected array  $methods         = [],
        protected bool   $isActive        = true,
        protected array  $config          = [],
        protected bool   $useConstructor  = true,
        protected array  $bindings        = [],
        protected array  $dependencies    = [],
        array            $attributes      = [],
        protected array  $includeTags     = [],
        protected array  $excludeTags     = [],
        protected string $packageName     = '',
        protected string $description     = ''
    ) {
        $this->attributes = $attributes;
    }

    #[\Override]
    public function useConstructor(): bool
    {
        return $this->useConstructor;
    }

    #[\Override]
    public function getDependencyDescriptors(): array
    {
        return $this->dependencies;
    }

    #[\Override]
    public function getPackageName(): string
    {
        return $this->packageName;
    }

    #[\Override]
    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    #[\Override]
    public function getClassName(): string
    {
        return $this->className;
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[\Override]
    public function getDependencyName(): string
    {
        return $this->className;
    }

    #[\Override]
    public function isServiceActive(): bool
    {
        return $this->isActive;
    }

    #[\Override]
    public function getBindings(): array
    {
        return $this->bindings;
    }

    #[\Override]
    public function getIncludeTags(): array
    {
        return $this->includeTags;
    }

    #[\Override]
    public function getExcludeTags(): array
    {
        return $this->excludeTags;
    }

    #[\Override]
    public function getServiceConfig(): array
    {
        return $this->config;
    }

    #[\Override]
    public function getServiceMethods(): array
    {
        return $this->methods;
    }

    #[\Override]
    public function findServiceMethod(string $method): ?FunctionDescriptorInterface
    {
        return $this->methods[$method] ?? null;
    }

    #[\Override]
    public function getServiceMethod(string $method): FunctionDescriptorInterface
    {
        return $this->methods[$method] ?? throw new MethodNotFound($this->serviceName, $method);
    }

    #[\Override]
    public function provideConfiguration(): array|null
    {
        return $this->config;
    }
}
