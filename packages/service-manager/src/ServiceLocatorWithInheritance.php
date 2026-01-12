<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\DI\AliasInitializer;
use IfCastle\DI\Container;
use IfCastle\DI\ContainerInterface;
use IfCastle\DI\Resolver;
use IfCastle\ServiceManager\Exceptions\ServiceNotFound;

class ServiceLocatorWithInheritance extends Container implements ServiceLocatorInterface
{
    public function __construct(
        protected readonly DescriptorRepositoryInterface $descriptorRepository,
        ?ContainerInterface                               $parentContainer = null,
        bool                                             $isWeakParent = false
    ) {
        parent::__construct(new Resolver(), [], $parentContainer, $isWeakParent);
    }

    #[\Override]
    public function getServiceList(bool $shouldUpdate = false): array
    {
        $services                   = [];

        foreach ($this->descriptorRepository->getServiceDescriptorList() as $serviceDescriptor) {
            $services[$serviceDescriptor->getServiceName()] = $this->getService($serviceDescriptor->getServiceName());
        }

        $parentContainer            = $this->getParentContainer();

        if ($parentContainer instanceof ServiceLocatorInterface) {

            foreach (\array_keys($parentContainer->getServiceDescriptorList()) as $serviceName) {
                if (false === \array_key_exists($serviceName, $services)) {
                    $services[$serviceName] = $parentContainer->getService($serviceName);
                }
            }
        }

        return $services;
    }

    #[\Override]
    public function findService(string $serviceName): ?object
    {
        return $this->tryLoadService($serviceName);
    }

    #[\Override]
    public function getService(string $serviceName): object
    {
        return $this->tryLoadService($serviceName) ?? throw new ServiceNotFound($serviceName);
    }

    #[\Override]
    public function findServiceClass(string $serviceName): string|null
    {
        $descriptor                 = $this->descriptorRepository->findServiceDescriptor($serviceName);

        if ($descriptor !== null) {
            return $descriptor->getClassName();
        }

        $parentContainer            = $this->getParentContainer();

        if ($parentContainer instanceof ServiceLocatorInterface) {
            return $parentContainer->findServiceClass($serviceName);
        }

        return null;
    }

    #[\Override]
    public function getServiceDescriptorList(bool $onlyActive = true): array
    {
        $descriptors                = [];

        foreach ($this->descriptorRepository->getServiceDescriptorList($onlyActive) as $serviceDescriptor) {
            $descriptors[$serviceDescriptor->getServiceName()] = $serviceDescriptor;
        }

        $parentContainer            = $this->getParentContainer();

        if ($parentContainer instanceof ServiceLocatorInterface) {
            foreach ($parentContainer->getServiceDescriptorList($onlyActive) as $serviceName => $serviceDescriptor) {
                if (false === \array_key_exists($serviceName, $descriptors)) {
                    $descriptors[$serviceName] = $serviceDescriptor;
                }
            }
        }

        return $descriptors;
    }

    #[\Override]
    public function findServiceDescriptor(string $serviceName): ServiceDescriptorInterface|null
    {
        $descriptor                 = $this->descriptorRepository->findServiceDescriptor($serviceName);

        if ($descriptor !== null) {
            return $descriptor;
        }

        $parentContainer            = $this->getParentContainer();

        if ($parentContainer instanceof ServiceLocatorInterface) {
            return $parentContainer->findServiceDescriptor($serviceName);
        }

        return null;
    }

    #[\Override]
    public function getServiceDescriptor(string $serviceName): ServiceDescriptorInterface
    {
        return $this->findServiceDescriptor($serviceName) ?? throw new ServiceNotFound($serviceName);
    }

    public function tryLoadService(string $serviceName): object|null
    {
        if (\array_key_exists($serviceName, $this->container)) {
            return parent::findDependency($serviceName);
        }

        $serviceDescriptor          = $this->descriptorRepository->findServiceDescriptor($serviceName);

        if (null === $serviceDescriptor) {

            $parentContainer        = $this->getParentContainer();

            if ($parentContainer instanceof ServiceLocatorInterface) {
                return $parentContainer->findService($serviceName);
            }

            return null;
        }

        $this->container[$serviceName] = $serviceDescriptor;

        foreach ($serviceDescriptor->getBindings() as $interface) {

            if (\array_key_exists($interface, $this->container)) {
                continue;
            }

            $this->container[$interface] = new AliasInitializer($serviceName);
        }

        return parent::findDependency($serviceName);
    }
}
