<?php

declare(strict_types=1);

namespace IfCastle\Application\MockApplication;

use IfCastle\Application\Bootloader\BootloaderExecutorInterface;
use IfCastle\Application\Bootloader\BootloaderInterface;
use IfCastle\Application\EngineInterface;
use IfCastle\Application\NativeEngine;

final class TestBootloader implements BootloaderInterface
{
    #[\Override]
    public function buildBootloader(BootloaderExecutorInterface $bootloaderExecutor): void
    {
        $bootloaderExecutor->getBootloaderContext()->getSystemEnvironmentBootBuilder()
                                                   ->bindConstructible(EngineInterface::class, NativeEngine::class);
    }
}
