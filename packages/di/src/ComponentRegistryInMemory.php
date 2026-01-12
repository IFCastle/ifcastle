<?php

declare(strict_types=1);

namespace IfCastle\DI;

use IfCastle\DI\Exceptions\ConfigException;

class ComponentRegistryInMemory implements ComponentRegistryMutableInterface
{
    /**
     * @param array<string, ConfigInterface> $registry
     */
    public function __construct(
        protected array   $registry       = [],
        protected bool    $isReadOnly     = false,
        protected bool    $wasModified    = false
    ) {}

    #[\Override]
    public function getComponentNames(): array
    {
        return \array_keys($this->registry);
    }

    /**
     * @throws ConfigException
     */
    #[\Override]
    public function getComponentConfig(string $componentName): ConfigInterface
    {
        return $this->registry[$componentName] ?? throw new ConfigException("Component '$componentName' not found.");
    }

    #[\Override]
    public function findComponentConfig(string $componentName): ConfigInterface|null
    {
        return $this->registry[$componentName] ?? null;
    }

    /**
     * @throws ConfigException
     */
    #[\Override]
    public function addComponentConfig(string $componentName, ConfigInterface $config): static
    {
        $this->throwReadOnly($componentName);
        $this->wasModified          = true;
        $this->registry[$componentName] = $config;
        return $this;
    }

    /**
     * @throws ConfigException
     */
    #[\Override]
    public function removeComponentConfig(string $componentName): static
    {
        $this->throwReadOnly($componentName);
        $this->wasModified          = true;
        unset($this->registry[$componentName]);
        return $this;
    }

    #[\Override]
    public function findComponentConfigMutable(string $componentName): ConfigMutableInterface|null
    {
        $config                     = $this->registry[$componentName] ?? null;

        if ($config instanceof ConfigMutableInterface) {
            return $config;
        }

        return null;
    }

    #[\Override]
    public function asImmutable(): static
    {
        $this->isReadOnly           = true;
        return $this;
    }

    #[\Override]
    public function cloneAsMutable(): static
    {
        /* @phpstan-ignore-next-line */
        return new self($this->registry, false, $this->wasModified);
    }

    /**
     * @throws ConfigException
     */
    protected function throwReadOnly(string $node = ''): void
    {
        if ($this->isReadOnly) {
            throw new ConfigException('The config key ' . $node . ' is read only');
        }
    }
}
