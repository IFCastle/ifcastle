<?php

declare(strict_types=1);

namespace IfCastle\TrueAsync;

use IfCastle\Application\Bootloader\BootloaderExecutorInterface;
use IfCastle\Application\Bootloader\BootloaderInterface;
use IfCastle\Application\EngineInterface;
use IfCastle\Async\CoroutineContextInterface;
use IfCastle\Async\CoroutineSchedulerInterface;

/**
 * Binds the TrueAsync engine, coroutine context and scheduler unless another engine is already bound.
 */
final class Bootloader implements BootloaderInterface
{
    #[\Override]
    public function buildBootloader(BootloaderExecutorInterface $bootloaderExecutor): void
    {
        $builder                    = $bootloaderExecutor->getBootloaderContext()->getSystemEnvironmentBootBuilder();

        if ($builder->isBound(EngineInterface::class)) {
            return;
        }

        $builder->bindConstructible(EngineInterface::class, TrueAsyncEngine::class, isThrow: false)
                ->bindConstructible(CoroutineContextInterface::class, CoroutineContext::class)
                ->bindConstructible(CoroutineSchedulerInterface::class, CoroutineScheduler::class);
    }
}
