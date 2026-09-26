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
     * The active configuration of every service, keyed by service name; a service without an
     * active implementation is absent.
     *
     * @return array<string, array<mixed>>
     */
    public function getServicesConfig(): array;

    /**
     * @return array<string, mixed>|null the configuration of one service, null when the name is unknown
     */
    public function findServiceConfig(string $serviceName): array|null;
}
