<?php

declare(strict_types=1);

namespace IfCastle\DI;

interface ComponentRegistryMutableInterface extends ComponentRegistryInterface
{
    public function addComponentConfig(string $componentName, ConfigInterface $config): static;

    public function removeComponentConfig(string $componentName): static;

    public function findComponentConfigMutable(string $componentName): ConfigMutableInterface|null;

    public function asImmutable(): static;

    public function cloneAsMutable(): static;
}
