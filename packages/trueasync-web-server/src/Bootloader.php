<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer;

use IfCastle\Application\Bootloader\BootloaderExecutorInterface;
use IfCastle\Application\Bootloader\BootloaderInterface;
use IfCastle\Application\EngineInterface;
use IfCastle\Async\CoroutineContextInterface;
use IfCastle\Async\CoroutineSchedulerInterface;
use IfCastle\TrueAsync\CoroutineContext;
use IfCastle\TrueAsync\CoroutineScheduler;

/**
 * Makes WebServerEngine the engine of the application in place of the plain TrueAsync engine,
 * with the TrueAsync coroutine context and scheduler. It runs before or after
 * IfCastle\TrueAsync\Bootloader alike: that one binds nothing once an engine is bound.
 */
final class Bootloader implements BootloaderInterface
{
    #[\Override]
    public function buildBootloader(BootloaderExecutorInterface $bootloaderExecutor): void
    {
        $bootloaderExecutor->getBootloaderContext()->getSystemEnvironmentBootBuilder()
                           ->bindConstructible(EngineInterface::class, WebServerEngine::class, redefine: true)
                           ->bindConstructible(CoroutineContextInterface::class, CoroutineContext::class, isThrow: false)
                           ->bindConstructible(CoroutineSchedulerInterface::class, CoroutineScheduler::class, isThrow: false);
    }
}
