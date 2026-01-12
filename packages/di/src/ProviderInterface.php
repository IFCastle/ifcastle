<?php

declare(strict_types=1);

namespace IfCastle\DI;

/**
 * An interface that provides a dependency based on its descriptor, container, and dependency.
 */
interface ProviderInterface
{
    /**
     * Provides a dependency based on its descriptor, container, and dependency.
     *
     * @param array<class-string> $resolvingKeys list of classes that are currently being resolved
     */
    public function provide(
        ContainerInterface $container,
        DescriptorInterface $descriptor,
        ?DependencyInterface $forDependency = null,
        array $resolvingKeys = []
    ): mixed;
}
