<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager\RepositoryStorages;

final class RepositoryReaderByTagsBridge implements RepositoryReaderInterface
{
    /**
     * @var array<string, array<mixed>>|null
     */
    private array|null $servicesConfig = null;

    /**
     * @param array<string> $tags
     */
    public function __construct(private readonly ServiceCollectionInterface $repositoryReader, private readonly array $tags) {}

    #[\Override]
    public function getServicesConfig(): array
    {
        if ($this->servicesConfig !== null) {
            return $this->servicesConfig;
        }

        $this->servicesConfig       = [];
        $results                    = [];

        foreach ($this->repositoryReader->getServiceCollection(tags: $this->tags) as $serviceName => $collection) {
            foreach ($collection as $serviceConfig) {
                $results[$serviceName] = $serviceConfig;
                break;
            }
        }

        $this->servicesConfig       = $results;

        return $results;
    }

    #[\Override]
    public function findServiceConfig(string $serviceName): array|null
    {
        if ($this->servicesConfig === null) {
            $this->getServicesConfig();
        }

        return $this->servicesConfig[$serviceName] ?? null;
    }
}
