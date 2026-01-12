<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\ServiceManager;

use IfCastle\Application\Environment\PublicEnvironmentInterface;
use IfCastle\DI\ContainerInterface;
use IfCastle\DI\InitializerAbstract;
use IfCastle\ServiceManager\AccessCheckerInterface;
use IfCastle\ServiceManager\InternalExecutor;
use IfCastle\ServiceManager\ServiceLocatorInterface;
use IfCastle\ServiceManager\ServiceTracerInterface;
use IfCastle\ServiceManager\TaskRunnerInterface;

final class InternalExecutorInitializer extends InitializerAbstract
{
    #[\Override]
    protected function initialize(ContainerInterface $container, array $resolvingKeys = []): mixed
    {
        $publicEnvironment          = $container->resolveDependency(PublicEnvironmentInterface::class, resolvingKeys: $resolvingKeys);

        return new InternalExecutor(
            $publicEnvironment->resolveDependency(ServiceLocatorInterface::class),
            $container->resolveDependency(ServiceLocatorInterface::class),
            $container,
            $container->findDependency(AccessCheckerInterface::class),
            $container->findDependency(TaskRunnerInterface::class),
            $container->findDependency(ServiceTracerInterface::class)
        );
    }
}
