<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager\RepositoryStorages;

/**
 * ## RepositoryReaderInterface.
 *
 * Provides reading of service descriptors to be loaded into the Environment.
 * This interface organizes access to services by their unique service names.
 */
interface RepositoryReaderInterface
{
    /**
     * @return array<string, array<mixed>>
     */
    public function getServicesConfig(): array;

    /**
     * @return array<string, array<mixed>>
     */
    public function findServiceConfig(string $serviceName): array|null;
}
