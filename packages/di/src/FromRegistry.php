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
    /**
     * True when the attribute names a component; the key of an attribute without one is later
     * filled with the parameter name, which names no component.
     */
    private readonly bool $hasComponentKey;

    public function __construct(string $key = '', mixed ...$dependency)
    {
        $this->hasComponentKey      = $key !== '';

        parent::__construct($key, ...$dependency);
    }

    #[\Override]
    public function getProvider(): ProviderInterface
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

        $registry                   = $container->findDependency(ComponentRegistryInterface::class);

        if ($registry !== null && $registry instanceof ComponentRegistryInterface === false) {
            throw new \TypeError('Registry is not an instance of ' . ComponentRegistryInterface::class);
        }

        // A named component is the only source: a typo must give null, not someone else's config.
        if ($this->hasComponentKey) {
            return $registry?->findComponentConfig($this->getDependencyKey());
        }

        if ($forDependency instanceof ConfigurationProviderInterface) {
            $config                 = $forDependency->provideConfiguration();

            if ($config !== null) {
                return $config;
            }
        }

        if ($forDependency === null) {
            return null;
        }

        return $registry?->findComponentConfig($forDependency->getDependencyName());
    }
}
