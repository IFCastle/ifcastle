<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\ServiceManager;

use IfCastle\DI\ContainerInterface;
use IfCastle\DI\InitializerAbstract;
use IfCastle\ServiceManager\AccessCheckerInterface;
use IfCastle\ServiceManager\PublicExecutor;
use IfCastle\ServiceManager\ServiceLocatorInterface;
use IfCastle\ServiceManager\ServiceTracerInterface;
use IfCastle\ServiceManager\TaskRunnerInterface;

final class PublicExecutorInitializer extends InitializerAbstract
{
    #[\Override]
    protected function initialize(ContainerInterface $container, array $resolvingKeys = []): PublicExecutor
    {
        return                 new PublicExecutor(
            $container->resolveDependency(ServiceLocatorInterface::class),
            $container,
            $container->findDependency(AccessCheckerInterface::class),
            $container->findDependency(TaskRunnerInterface::class),
            $container->findDependency(ServiceTracerInterface::class)
        );
    }
}
