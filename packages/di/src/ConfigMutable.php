<?php

declare(strict_types=1);

namespace IfCastle\DI;

use IfCastle\DI\Exceptions\ConfigException;

class ConfigMutable implements ConfigMutableInterface
{
    /**
     * @param array<string, scalar|scalar[]|null> $config
     */
    public function __construct(
        protected array   $config         = [],
        protected bool    $isReadOnly     = false,
        protected bool    $wasModified    = false
    ) {}

    #[\Override]
    public function findValue(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    #[\Override]
    public function findSection(string $section): array
    {
        return $this->config[$section] ?? [];
    }

    /**
     * @throws ConfigException
     */
    #[\Override]
    public function requireValue(string $key): mixed
    {
        if (!\array_key_exists($key, $this->config)) {
            throw new ConfigException('The config key ' . $key . ' is required');
        }

        return $this->config[$key];
    }

    /**
     * @throws ConfigException
     */
    #[\Override]
    public function requireSection(string $section): array
    {
        if (!\array_key_exists($section, $this->config) || !\is_array($this->config[$section])) {
            throw new ConfigException('The config key ' . $section . ' is required');
        }

        return $this->config[$section];
    }

    /**
     * @throws ConfigException
     */
    #[\Override]
    public function set(string $node, mixed $value): static
    {
        $this->throwReadOnly();
        $this->wasModified          = true;

        $this->config[$node]        = $value;

        return $this;
    }

    /**
     * @throws ConfigException
     */
    #[\Override]
    public function setSection(string $node, array $value): static
    {
        $this->throwReadOnly();
        $this->wasModified          = true;

        $this->config[$node]        = $value;

        return $this;
    }

    /**
     * @throws ConfigException
     */
    #[\Override]
    public function merge(array $config): static
    {
        $this->throwReadOnly();
        $this->wasModified         = true;

        $this->config               = \array_merge($this->config, $config);

        return $this;
    }

    /**
     * @throws ConfigException
     */
    #[\Override]
    public function mergeSection(string $node, array $config): static
    {
        $this->throwReadOnly($node);
        $this->wasModified          = true;

        if (\array_key_exists($node, $this->config) && \is_array($this->config[$node])) {
            $this->config[$node]    = \array_merge($this->config[$node], $config);
        } else {
            $this->config[$node]    = $config;
        }

        return $this;
    }

    /**
     * @throws ConfigException
     */
    #[\Override]
    public function remove(string ...$path): static
    {
        $this->throwReadOnly();

        $current                    = &$this->config;

        while ($path !== []) {
            $key                    = \array_shift($path);

            if (!\is_array($current) || !\array_key_exists($key, $current)) {
                return $this;
            }

            $current                = &$current[$key];
        }

        $this->wasModified          = true;

        unset($current);

        return $this;
    }

    /**
     * @throws ConfigException
     */
    #[\Override]
    public function reset(): static
    {
        $this->throwReadOnly();
        $this->wasModified          = true;

        $this->config               = [];

        return $this;
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
        return new self($this->config, false, $this->wasModified);
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
