<?php

declare(strict_types=1);

namespace IfCastle\DI;

/**
 * ## DependencyContract.
 *
 * Dependency contract: a special descriptor that allows interfaces and classes
 * to define how dependencies are resolved and descriptors are created.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class DependencyContract
{
    public function __construct(
        public ProviderInterface|null $provider = null,
        public DescriptorProviderInterface|null $descriptorProvider = null,
        public bool $isLazy = false,
    ) {}
}
