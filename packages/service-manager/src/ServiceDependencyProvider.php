<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\DI\ContainerInterface;
use IfCastle\DI\DependencyInterface;
use IfCastle\DI\DescriptorInterface;
use IfCastle\DI\ProviderInterface;

/**
 * ## ServiceDependencyProvider.
 *
 * A strategy for finding a service by type.
 * Algorithm:
 *
 * * Retrieve the ServiceLocatorInterface from the dependency container.
 * * Attempt to find a service matching one of the dependency types; otherwise, return NULL.
 */
final class ServiceDependencyProvider implements ProviderInterface
{
    /**
     * @throws \Throwable
     */
    #[\Override]
    public function provide(
        ContainerInterface $container,
        DescriptorInterface $descriptor,
        ?DependencyInterface $forDependency = null,
        array $resolvingKeys = [],
    ): mixed {

        $serviceLocator             = $container->findDependency(ServiceLocatorInterface::class);

        if ($serviceLocator === null) {
            return null;
        }

        if ($serviceLocator instanceof ContainerInterface) {
            return $serviceLocator->findDependency($descriptor, $forDependency, resolvingKeys: $resolvingKeys);
        }

        $types                      = $descriptor->getDependencyType();

        if ($types === null) {
            return null;
        }

        foreach (\is_string($types) ? [$types] : $types as $dependencyType) {
            $service                = $serviceLocator->findService($dependencyType);

            if ($service !== null) {
                return $service;
            }
        }

        return null;
    }
}
