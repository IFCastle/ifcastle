<?php

declare(strict_types=1);

namespace IfCastle\Application\Environment;

use IfCastle\DI\ContainerInterface;
use IfCastle\DI\ContainerMutableInterface;

interface EnvironmentInterface extends ContainerInterface, ContainerMutableInterface
{
    public function get(string $key): mixed;

    public function isExist(string $key): bool;

    public function find(string ...$path): mixed;

    public function is(string ...$path): bool;

    public function destroy(string $key): static;

    /**
     * @param array<string, mixed> $data
     *
     * @return $this
     */
    public function merge(array $data): static;

    public function getParentEnvironment(): ?EnvironmentInterface;
}
