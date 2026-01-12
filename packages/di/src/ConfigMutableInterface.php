<?php

declare(strict_types=1);

namespace IfCastle\DI;

interface ConfigMutableInterface extends ConfigInterface
{
    public function set(string $node, mixed $value): static;

    /**
     * @param array<string, scalar|scalar[]|null> $value
     */
    public function setSection(string $node, array $value): static;

    /**
     * @param array<string, scalar|scalar[]|null> $config
     */
    public function merge(array $config): static;

    /**
     * @param array<string, scalar|scalar[]|null> $config
     */
    public function mergeSection(string $node, array $config): static;

    public function remove(string ...$path): static;

    public function reset(): static;

    public function asImmutable(): static;

    public function cloneAsMutable(): static;
}
