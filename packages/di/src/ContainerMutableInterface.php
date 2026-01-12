<?php

declare(strict_types=1);

namespace IfCastle\DI;

/**
 * A dependency container that can be mutable.
 */
interface ContainerMutableInterface extends ContainerInterface
{
    /**
     * @template Class
     * @param class-string<Class>|string $key
     * @param ($key is class-string ? Class : scalar|array<scalar>|null) $value
     */
    public function set(string $key, mixed $value): static;

    /**
     * @param class-string|string $key
     */
    public function delete(string $key): static;
}
