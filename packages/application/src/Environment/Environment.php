<?php

declare(strict_types=1);

namespace IfCastle\Application\Environment;

use IfCastle\DI\Container;
use IfCastle\DI\DependencyInterface;
use IfCastle\DI\DisposableInterface;
use IfCastle\DI\InitializerInterface;

class Environment extends Container implements EnvironmentInterface
{
    #[\Override]
    public function get(string $key): mixed
    {
        // Initialize an object if initializer was defined
        if (\array_key_exists($key, $this->container) &&
           ($this->container[$key] instanceof InitializerInterface || $this->container[$key] instanceof DependencyInterface)) {
            return $this->findDependency($key);
        }

        return $this->container[$key] ?? ($this->getParentEnvironment()?->get($key));
    }

    #[\Override]
    public function isExist(string $key): bool
    {
        return \array_key_exists($key, $this->container);
    }


    #[\Override]
    public function find(string ...$path): mixed
    {
        if ($path === []) {
            return null;
        }

        $current                    = $this->container;

        while ($path !== []) {
            $key                    = \array_shift($path);

            if (!\is_array($current) || !\array_key_exists($key, $current)) {
                return null;
            }

            $current                = $current[$key];
        }

        return $current;
    }


    #[\Override]
    public function is(string ...$path): bool
    {
        return !empty($this->find(...$path));
    }

    /**
     * @return  $this
     */
    #[\Override]
    public function set(string $key, mixed $value): static
    {
        $this->container[$key]           = $value;
        return $this;
    }

    /**
     * @return  $this
     */
    #[\Override]
    public function delete(string $key): static
    {
        if (\array_key_exists($key, $this->container)) {
            unset($this->container[$key]);
        }

        return $this;
    }

    #[\Override]
    public function destroy(string $key): static
    {
        if (\array_key_exists($key, $this->container)) {

            if ($this->container[$key] instanceof DisposableInterface) {
                $data               = $this->container[$key];
                unset($this->container[$key]);
                $data->dispose();
            } else {
                unset($this->container[$key]);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    #[\Override]
    public function merge(array $data): static
    {
        $this->container                 = \array_merge($this->container, $data);
        return $this;
    }

    #[\Override]
    public function getParentEnvironment(): ?EnvironmentInterface
    {
        $parent                         = $this->getParentContainer();

        if ($parent instanceof EnvironmentInterface) {
            return $parent;
        }

        return null;
    }
}
