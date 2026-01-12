<?php

declare(strict_types=1);

namespace IfCastle\DI;

/**
 * Interface ConfigurationProviderInterface.
 *
 * This interface allows describing a situation where an object itself holds
 * configuration information and can provide it.
 * This interface can be useful when configuration needs to be specified directly
 * within the dependency descriptor.
 *
 * Together with the FromRegistry attribute, it enables configuring the service through the dependency descriptor.
 *
 * @package IfCastle\DI
 */
interface ConfigurationProviderInterface
{
    /**
     * Provides the configuration.
     *
     * @return mixed[]|null
     */
    public function provideConfiguration(): array|null;
}
