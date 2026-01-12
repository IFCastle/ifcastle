<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

/**
 * ## DescriptorRepositoryInterface.
 *
 * Provides access to service descriptors.
 *
 * `DescriptorRepositoryInterface` uses the `RepositoryReaderInterface` to construct a service descriptor
 * as a Dependency Injection object, which can then be resolved and subsequently
 * loaded into memory.
 */
interface DescriptorRepositoryInterface
{
    public function findServiceClass(string $serviceName): string|null;

    /**
     *
     * @return ServiceDescriptorInterface[]
     */
    public function getServiceDescriptorList(bool $onlyActive = true): array;

    public function findServiceDescriptor(string $serviceName): ServiceDescriptorInterface|null;

    public function getServiceDescriptor(string $serviceName): ServiceDescriptorInterface;
}
