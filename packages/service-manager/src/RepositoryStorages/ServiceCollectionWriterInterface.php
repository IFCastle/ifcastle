<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager\RepositoryStorages;

/**
 * From the consumer's perspective, the service name should be a unique key that unequivocally identifies the service.
 * However, from the system's perspective,
 * a single name may correspond to multiple services depending on the scope/execution environment.
 *
 * Therefore, the service repository uses both the `package name` and the `service name` to uniquely identify a service.
 *
 * If a package defines MULTIPLE services, it then uses the `$serviceSuffix` parameter.
 */
interface ServiceCollectionWriterInterface extends RepositoryReaderInterface, ServiceCollectionInterface
{
    /**
     * Adds a new service configuration to the repository.
     *
     * If $serviceSuffix is not provided, the service is considered to be the default service.
     *
     * @param array<string, mixed> $serviceConfig
     * @param ?array<string>        $includeTags
     * @param ?array<string>        $excludeTags
     */
    public function addServiceConfig(
        string     $packageName,
        string     $serviceName,
        array      $serviceConfig,
        bool       $isActive = true,
        array|null $includeTags = null,
        array|null $excludeTags = null,
        string|null $serviceSuffix = null
    ): void;

    public function removeServiceConfig(string $packageName, string $serviceName): void;

    /**
     * Updates the service configuration in the repository.
     *
     * @param array<string, mixed> $serviceConfig
     * @param ?array<string>        $includeTags
     * @param ?array<string>        $excludeTags
     */
    public function updateServiceConfig(string     $packageName,
        string     $serviceName,
        array      $serviceConfig,
        array|null $includeTags = null,
        array|null $excludeTags = null,
        string|null $serviceSuffix = null
    ): void;

    public function activateService(string $packageName, string $serviceName, string $serviceSuffix): void;

    public function deactivateService(string $packageName, string $serviceName, string $serviceSuffix): void;

    /**
     * Changes the tags of a service.
     *
     * @param ?array<string> $includeTags
     * @param ?array<string> $excludeTags
     */
    public function changeServiceTags(
        string $packageName,
        string $serviceName,
        string $serviceSuffix,
        array|null $includeTags = null,
        array|null $excludeTags = null
    ): void;

    public function saveRepository(): void;
}
