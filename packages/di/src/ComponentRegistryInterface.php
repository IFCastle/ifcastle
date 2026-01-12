<?php

declare(strict_types=1);

namespace IfCastle\DI;

/**
 * The interface defines the concept of a "Component Registry."
 * The Component Registry is a unified storage of component configurations that are NOT defined by the USER/HUMAN.
 *
 * The difference from configuration is that the component registry is configured only programmatically.
 * The registry is usually used for registering classes, services, etc.
 */
interface ComponentRegistryInterface
{
    /**
     * Returns the names of all components registered in the registry.
     *
     * @return string[]
     */
    public function getComponentNames(): array;

    public function getComponentConfig(string $componentName): ConfigInterface;

    public function findComponentConfig(string $componentName): ConfigInterface|null;
}
