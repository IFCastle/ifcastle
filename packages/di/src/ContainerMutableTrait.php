<?php

declare(strict_types=1);

namespace IfCastle\DI;

trait ContainerMutableTrait
{
    protected array $container;

    public function set(string $key, mixed $value): static
    {
        $this->container[$key]      = $value;
        return $this;
    }

    public function delete(string $key): static
    {
        if (\array_key_exists($key, $this->container)) {
            unset($this->container[$key]);
        }

        return $this;
    }
}
