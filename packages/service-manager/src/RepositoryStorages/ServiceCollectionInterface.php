<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager\RepositoryStorages;

/**
 * Interface for accessing the complete collection of services,
 * where a single service name may have multiple alternatives.
 *
 * The collection is indexed by service name and service suffix.
 *
 * The Collection of services describes a list of services distributed according to the following characteristics:
 *
 * * Service name – describes the name of the service, which is unique within the runtime environment.
 * In other words, only one service with the specified name can be loaded into an environment.
 * * Package name – the name of the package that defines the service.
 * * Runtime tags – specify the tags under which the service should be selected.
 * * Exclude tags – specify conditions under which the service should not be loaded.
 */
interface ServiceCollectionInterface
{
    public const string NAME        = '_service_name_';

    public const string IS_ACTIVE   = 'isActive';

    public const string PACKAGE     = 'package';

    public const string CLASS_NAME  = 'class';

    public const string TAGS        = 'tags';

    public const string EXCLUDE_TAGS = 'excludeTags';

    public const string DESCRIPTION = 'description';

    /**
     * Returns services configuration with format:
     * [
     *    'service_name' => [
     *        'service_suffix' => [
     *      ],
     *   ],
     * ]
     * @param string[] $tags
     *
     * @return array<string, array<string, array<mixed>>>
     */
    public function getServiceCollection(
        string|null $serviceName = null,
        string|null $packageName = null,
        string|null $suffix = null,
        bool|null   $isActive = null,
        array       $tags = []
    ): array;
}
