<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

/**
 * ## ServiceLocatorInterface.
 *
 * Provides access to services.
 *
 * `ServiceLocatorInterface` uses the `DescriptorRepositoryInterface` to construct a service descriptor
 * as a Dependency Injection object, which can then be resolved and subsequently
 * loaded into memory.
 */
interface ServiceLocatorInterface extends DescriptorRepositoryInterface
{
    /**
     * @return array<string, object>
     */
    public function getServiceList(bool $shouldUpdate = false): array;

    public function findService(string $serviceName): ?object;

    public function getService(string $serviceName): object;
}
