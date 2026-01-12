<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\ServiceManager\Exceptions\ServiceConfigException;
use IfCastle\ServiceManager\Exceptions\ServiceNotFound;
use IfCastle\ServiceManager\RepositoryStorages\RepositoryReaderInterface;
use IfCastle\ServiceManager\RepositoryStorages\ServiceCollectionInterface;
use IfCastle\TypeDefinitions\Resolver\ResolverInterface;

class DescriptorRepository implements DescriptorRepositoryInterface
{
    /**
     * @var array<string, ServiceDescriptorInterface>|null
     */
    protected array|null $serviceDescriptors = null;

    /**
     * If $useOnlyServiceMethods is set to true,
     * only methods with @ServiceMethod attribute will be considered as service methods.
     * In another case all public methods will be considered as service methods.
     */
    public function __construct(
        protected readonly RepositoryReaderInterface         $repositoryReader,
        protected readonly ResolverInterface                 $resolver,
        protected readonly ServiceDescriptorBuilderInterface $descriptorBuilder,
        protected readonly bool                              $useOnlyServiceMethods  = true,
        protected readonly bool                              $bindWithFirstInterface = false,
        protected readonly bool                              $bindWithAllInterfaces  = false
    ) {}

    #[\Override]
    public function findServiceClass(string $serviceName): string|null
    {
        $this->load();

        $service                    = $this->serviceDescriptors[$serviceName] ?? null;
        return $service?->getClassName();
    }

    #[\Override]
    public function getServiceDescriptorList(bool $onlyActive = true): array
    {
        $this->load();

        return $this->serviceDescriptors;
    }

    #[\Override]
    public function findServiceDescriptor(string $serviceName): ServiceDescriptorInterface|null
    {
        $this->load();

        return $this->serviceDescriptors[$serviceName] ?? null;
    }

    #[\Override]
    public function getServiceDescriptor(string $serviceName): ServiceDescriptorInterface
    {
        $this->load();

        return $this->serviceDescriptors[$serviceName] ?? throw new ServiceNotFound($serviceName);
    }

    /**
     * @throws ServiceConfigException
     */
    protected function load(): void
    {
        if ($this->serviceDescriptors !== null) {
            return;
        }

        $serviceDescriptors         = [];

        foreach ($this->repositoryReader->getServicesConfig() as $serviceName => $serviceConfig) {

            if (false === \array_key_exists(ServiceCollectionInterface::CLASS_NAME, $serviceConfig)) {
                throw new ServiceConfigException([
                    'template'      => 'Service {serviceName} has no class defined',
                    'serviceName'   => $serviceName,
                ]);
            }

            if (empty($serviceConfig[ServiceCollectionInterface::IS_ACTIVE])) {
                continue;
            }

            $serviceDescriptors[$serviceName] = $this->descriptorBuilder->buildServiceDescriptor(
                $serviceConfig[ServiceCollectionInterface::CLASS_NAME],
                $serviceName,
                $this->resolver,
                true,
                $serviceConfig,
                $this->useOnlyServiceMethods,
                $this->bindWithFirstInterface,
                $this->bindWithAllInterfaces
            );
        }

        $this->serviceDescriptors = $serviceDescriptors;
    }
}
