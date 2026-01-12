<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\ServiceManager\Exceptions\ServiceException;
use IfCastle\ServiceManager\RepositoryStorages\ServiceCollectionInterface;
use IfCastle\ServiceManager\RepositoryStorages\ServiceCollectionWriterInterface;

class ServiceManager implements ServiceManagerInterface
{
    public function __construct(
        protected ServiceCollectionWriterInterface $repositoryWriter
    ) {}

    /**
     * @throws ServiceException
     */
    #[\Override]
    public function installService(ServiceDescriptorInterface $serviceDescriptor): void
    {
        $serviceConfig = $this->buildConfigByServiceDescriptor($serviceDescriptor);
        $this->repositoryWriter->addServiceConfig($serviceDescriptor->getPackageName(), $serviceDescriptor->getServiceName(), $serviceConfig);
        $this->repositoryWriter->saveRepository();
    }

    /**
     * @throws ServiceException
     */
    #[\Override]
    public function uninstallService(string $serviceName, string $packageName): void
    {
        $this->repositoryWriter->removeServiceConfig($serviceName, $packageName);
        $this->repositoryWriter->saveRepository();
    }

    #[\Override]
    public function activateService(string $packageName, string $serviceName, string $suffix): void
    {
        $this->repositoryWriter->activateService($packageName, $serviceName, $suffix);
        $this->repositoryWriter->saveRepository();
    }

    #[\Override]
    public function deactivateService(string $packageName, string $serviceName, string $suffix): void
    {
        $this->repositoryWriter->deactivateService($packageName, $serviceName, $suffix);
        $this->repositoryWriter->saveRepository();
    }

    #[\Override]
    public function updateServiceConfig(ServiceDescriptorInterface $serviceDescriptor): void
    {
        $serviceConfig = $this->buildConfigByServiceDescriptor($serviceDescriptor);
        $this->repositoryWriter->updateServiceConfig($serviceDescriptor->getPackageName(), $serviceDescriptor->getServiceName(), $serviceConfig);
        $this->repositoryWriter->saveRepository();
    }

    /**
     * @return array<string, mixed>
     * @throws ServiceException
     */
    protected function buildConfigByServiceDescriptor(ServiceDescriptorInterface $serviceDescriptor): array
    {
        $serviceConfig              = $serviceDescriptor->getServiceConfig();
        $serviceConfig[ServiceCollectionInterface::CLASS_NAME] = $serviceDescriptor->getClassName();
        $serviceConfig[ServiceCollectionInterface::IS_ACTIVE]  = $serviceDescriptor->isServiceActive();
        $serviceConfig[ServiceCollectionInterface::TAGS]    = $serviceDescriptor->getIncludeTags();
        $serviceConfig[ServiceCollectionInterface::EXCLUDE_TAGS] = $serviceDescriptor->getExcludeTags();
        $serviceConfig[ServiceCollectionInterface::DESCRIPTION] = $serviceDescriptor->getDescription();

        return $serviceConfig;
    }
}
