<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\DI\DependencyContract;

/**
 * ## AsServiceContract.
 *
 * An attribute for interfaces or classes that indicates the given interface or class is a service.
 * This means that if the interface or class represents a dependency,
 * the dependency must be resolved according to the rules for services.
 *
 * @see https://github.com/EdmondDantes/di?tab=readme-ov-file#descriptor-provider
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class AsServiceContract extends DependencyContract
{
    public function __construct(bool $isLazy = false)
    {
        parent::__construct(new ServiceDependencyProvider(), null, $isLazy);
    }
}
