<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\Builder;

use IfCastle\DI\ConfigInterface;

final class ConfigInMemory implements ConfigInterface
{
    /**
     * @param mixed[] $data
     */
    public function __construct(public array $data = []) {}

    #[\Override]
    public function findValue(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    #[\Override]
    public function findSection(string $section): array
    {
        return $this->data[$section] ?? [];
    }

    /**
     * @throws \Exception
     */
    #[\Override]
    public function requireValue(string $key): mixed
    {
        if (!\array_key_exists($key, $this->data)) {
            throw new \Exception("Key $key not found in config");
        }

        return $this->data[$key];
    }

    /**
     * @throws \Exception
     */
    #[\Override]
    public function requireSection(string $section): array
    {
        if (!\array_key_exists($section, $this->data)) {
            throw new \Exception("Section $section not found in config");
        }

        return $this->data[$section];
    }
}
