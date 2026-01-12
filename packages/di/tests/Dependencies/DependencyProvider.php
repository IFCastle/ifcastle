<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

use IfCastle\DI\ContainerInterface;
use IfCastle\DI\DependencyInterface;
use IfCastle\DI\DescriptorInterface;
use IfCastle\DI\ProviderInterface;

final class DependencyProvider implements ProviderInterface
{
    #[\Override]
    public function provide(
        ContainerInterface $container,
        DescriptorInterface $descriptor,
        ?DependencyInterface $forDependency = null,
        array $resolvingKeys = [],
    ): mixed {
        return $container->resolveDependency('specificKey', $forDependency, 0, $resolvingKeys);
    }
}
