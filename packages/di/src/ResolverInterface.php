<?php

declare(strict_types=1);

namespace IfCastle\DI;

interface ResolverInterface
{
    public function canResolveDependency(DependencyInterface $dependency, ContainerInterface $container): bool;

    /**
     * @param array<class-string|string> $resolvingKeys list of classes|keys that are currently being resolved
     * @param bool                       $allowLazy    The parameter is TRUE if creating Lazy dependencies is allowed.
     */
    public function resolveDependency(
        DependencyInterface $dependency,
        ContainerInterface $container,
        string|DescriptorInterface $name,
        string $key,
        array $resolvingKeys        = [],
        bool $allowLazy             = false,
    ): mixed;
}
