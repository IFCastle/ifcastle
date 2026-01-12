<?php

declare(strict_types=1);

namespace IfCastle\Configurator\Toml;

use IfCastle\ServiceManager\RepositoryStorages\RepositoryReaderInterface;
use IfCastle\ServiceManager\RepositoryStorages\ServiceCollectionInterface;
use IfCastle\ServiceManager\ServiceConfigReaderTrait;

class ServiceConfig extends ConfigToml implements RepositoryReaderInterface, ServiceCollectionInterface
{
    use ServiceConfigReaderTrait;

    public function __construct(string $appDir)
    {
        parent::__construct($appDir . '/services.toml');
    }
}
