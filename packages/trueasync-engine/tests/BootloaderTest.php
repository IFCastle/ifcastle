<?php

declare(strict_types=1);

namespace IfCastle\TrueAsync;

use IfCastle\Application\Bootloader\BootloaderContextInterface;
use IfCastle\Application\Bootloader\BootloaderExecutorInterface;
use IfCastle\Application\EngineInterface;
use IfCastle\Async\CoroutineContextInterface;
use IfCastle\Async\CoroutineSchedulerInterface;
use IfCastle\DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;

class BootloaderTest extends TestCase
{
    public function testBindsEngineContextAndScheduler(): void
    {
        $builder                    = new ContainerBuilder();

        new Bootloader()->buildBootloader($this->executorFor($builder));

        $this->assertSame(TrueAsyncEngine::class, $builder->get(EngineInterface::class)->getClassName());
        $this->assertSame(CoroutineContext::class, $builder->get(CoroutineContextInterface::class)->getClassName());
        $this->assertSame(CoroutineScheduler::class, $builder->get(CoroutineSchedulerInterface::class)->getClassName());
    }

    public function testKeepsAnEngineBoundEarlier(): void
    {
        $builder                    = new ContainerBuilder();
        $builder->bindConstructible(EngineInterface::class, \stdClass::class);

        new Bootloader()->buildBootloader($this->executorFor($builder));

        $this->assertSame(\stdClass::class, $builder->get(EngineInterface::class)->getClassName());
        $this->assertFalse($builder->isBound(CoroutineSchedulerInterface::class));
    }

    private function executorFor(ContainerBuilder $builder): BootloaderExecutorInterface
    {
        $context                    = $this->createStub(BootloaderContextInterface::class);
        $context->method('getSystemEnvironmentBootBuilder')->willReturn($builder);

        $executor                   = $this->createStub(BootloaderExecutorInterface::class);
        $executor->method('getBootloaderContext')->willReturn($context);

        return $executor;
    }
}
