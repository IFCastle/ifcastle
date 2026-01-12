<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\ServiceManager;

use IfCastle\Application\Bootloader\BootloaderExecutorInterface;
use IfCastle\Application\Bootloader\BootloaderInterface;
use IfCastle\Application\Environment\PublicEnvironmentInterface;
use IfCastle\ServiceManager\ExecutorInterface;

final class ServiceExecutorPublicInternalBootloader implements BootloaderInterface
{
    #[\Override]
    public function buildBootloader(BootloaderExecutorInterface $bootloaderExecutor): void
    {
        $systemEnvironment          = $bootloaderExecutor->getBootloaderContext()->getSystemEnvironment();

        if ($systemEnvironment === null) {
            throw new \Exception('System environment is required for ServiceExecutorPublicInternalBootloader');
        }

        if (false === $systemEnvironment->hasDependency(ExecutorInterface::class)) {
            $systemEnvironment->set(ExecutorInterface::class, new InternalExecutorInitializer());
        }

        $this->initializePublicExecutor($systemEnvironment->resolveDependency(PublicEnvironmentInterface::class));
    }

    private function initializePublicExecutor(PublicEnvironmentInterface $publicEnvironment): void
    {
        if (false === $publicEnvironment->hasDependency(ExecutorInterface::class)) {
            $publicEnvironment->set(ExecutorInterface::class, new PublicExecutorInitializer());
        }
    }
}
