<?php

declare(strict_types=1);

namespace IfCastle\DI;

interface ConfigInterface
{
    public function findValue(string $key, mixed $default = null): mixed;

    /**
     * @return array<string, scalar|scalar[]|null>
     */
    public function findSection(string $section): array;

    public function requireValue(string $key): mixed;

    /**
     * @return array<string, scalar|scalar[]|null>
     */
    public function requireSection(string $section): array;
}
