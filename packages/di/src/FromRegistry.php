<?php

declare(strict_types=1);

namespace IfCastle\DI;

use Attribute;

/**
 * ## FromRegistry attribute.
 *
 * This attribute provides configuration based on the Registry.
 * The Registry is a special type of system configuration, distinct from the usual configuration,
 * and is component-oriented.
 *
 * This attribute also considers the special ConfigurationProviderInterface,
 * which can be implemented on a dependency descriptor.
 * In this case, the descriptor itself can provide the configuration for initialization.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class FromRegistry extends Dependency implements ProviderInterface
{
    #[\Override]
    public function getProvider(): ProviderInterface|null
    {
        return $this;
    }

    #[\Override]
    public function provide(
        ContainerInterface  $container,
        DescriptorInterface $descriptor,
        ?DependencyInterface $forDependency = null,
        array $resolvingKeys = []
    ): mixed {

        if ($forDependency instanceof ConfigurationProviderInterface) {
            $config                 = $forDependency->provideConfiguration();

            if ($config !== null) {
                return $config;
            }
        }

        $registry                     = $container->findDependency(ComponentRegistryInterface::class);

        if ($registry === null) {
            return null;
        }

        if ($registry instanceof ComponentRegistryInterface === false) {
            throw new \TypeError('Registry is not an instance of ' . ComponentRegistryInterface::class);
        }

        if ($forDependency === null) {
            return null;
        }

        return $registry->findComponentConfig($forDependency->getDependencyName());
    }
}
