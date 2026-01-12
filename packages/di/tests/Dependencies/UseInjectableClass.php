<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

use IfCastle\DI\DependencyInterface;
use IfCastle\DI\InjectableInterface;

final class UseInjectableClass implements UseInjectableInterface, InjectableInterface
{
    public static string $data = '';

    #[\Override]
    public function injectDependencies(array $dependencies, DependencyInterface $self): static
    {
        return $this;
    }

    #[\Override]
    public function initializeAfterInject(): static
    {
        return $this;
    }

    #[\Override]
    public function someMethod2(): void
    {
        self::$data = 'someMethod2';
    }
}
