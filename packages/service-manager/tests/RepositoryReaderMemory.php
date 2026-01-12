<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\ServiceManager\RepositoryStorages\RepositoryReaderInterface;
use IfCastle\ServiceManager\ServiceMocks\ServiceLibrary;
use IfCastle\ServiceManager\ServiceMocks\ServiceLibraryPublic;
use IfCastle\ServiceManager\ServiceMocks\ServiceMailer;

readonly class RepositoryReaderMemory implements RepositoryReaderInterface
{
    public static function buildForTest(): self
    {
        return new self([
            'ServiceLibrary'        => [
                'class'             => ServiceLibrary::class,
                'isActive'          => true,
                'config'            => [],
            ],
            'ServiceMailer'         => [
                'class'             => ServiceMailer::class,
                'isActive'          => true,
                'config'            => [],
            ],
            'ServiceMailerInactive' => [
                'class'             => 'fakeClass',
                'isActive'          => false,
                'config'            => [],
            ],
        ]);
    }

    public static function buildForPublicTest(): self
    {
        return new self([
            'ServiceLibraryPublic'  => [
                'class'             => ServiceLibraryPublic::class,
                'isActive'          => true,
                'config'            => [],
            ],
            'ServiceMailerPublic' => [
                'class'             => 'fakeClass',
                'isActive'          => false,
                'config'            => [],
            ],
            'ServiceLibrary'        => [
                'class'             => ServiceLibrary::class,
                'isActive'          => true,
                'config'            => [],
            ],
            'ServiceMailer'         => [
                'class'             => ServiceMailer::class,
                'isActive'          => true,
                'config'            => [],
            ],
            'ServiceMailerInactive' => [
                'class'             => 'fakeClass2',
                'isActive'          => false,
                'config'            => [],
            ],
        ]);
    }

    public function __construct(public array $servicesConfig = []) {}

    #[\Override]
    public function getServicesConfig(): array
    {
        return $this->servicesConfig;
    }

    #[\Override]
    public function findServiceConfig(string $serviceName): array|null
    {
        return $this->servicesConfig[$serviceName] ?? null;
    }
}
