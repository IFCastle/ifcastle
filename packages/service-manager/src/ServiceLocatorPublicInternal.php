<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\ServiceManager\Exceptions\ServiceNotFound;

/**
 * # ServiceLocatorPublicInternal.
 *
 * The locator implements an interface ServiceLocatorPublicInternalInterface
 * and allows searching for services based on their public status.
 */
class ServiceLocatorPublicInternal extends ServiceLocatorWithInheritance implements ServiceLocatorPublicInternalInterface
{
    #[\Override]
    public function getPublicServiceList(bool $shouldUpdate = false): array
    {
        $services                   = [];

        foreach ($this->descriptorRepository->getServiceDescriptorList() as $serviceDescriptor) {

            if ($serviceDescriptor->findAttribute(AsPublicService::class) === null) {
                continue;
            }

            $services[$serviceDescriptor->getServiceName()] = $this->getService($serviceDescriptor->getServiceName());
        }

        $parentContainer            = $this->getParentContainer();

        if ($parentContainer instanceof ServiceLocatorInterface) {

            foreach ($parentContainer->getServiceDescriptorList() as $serviceName => $serviceDescriptor) {
                if (false === \array_key_exists($serviceName, $services) && $serviceDescriptor->findAttribute(AsPublicService::class) !== null) {
                    $services[$serviceName] = $parentContainer->getService($serviceName);
                }
            }
        }

        return $services;
    }

    #[\Override]
    public function findPublicService(string $serviceName): ?object
    {
        $service                    = $this->findService($serviceName);

        if ($service === null) {
            return null;
        }

        $serviceDescriptor          = $this->descriptorRepository->findServiceDescriptor($serviceName);

        if ($serviceDescriptor->findAttribute(AsPublicService::class) === null) {
            return null;
        }

        return $service;
    }

    #[\Override]
    public function getPublicService(string $serviceName): object
    {
        $service                    = $this->findPublicService($serviceName);

        if ($service === null) {
            throw new ServiceNotFound($serviceName);
        }

        return $service;
    }
}
