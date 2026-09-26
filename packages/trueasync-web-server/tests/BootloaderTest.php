<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer;

use IfCastle\Application\Bootloader\BootloaderContextInterface;
use IfCastle\Application\Bootloader\BootloaderExecutorInterface;
use IfCastle\Application\Bootloader\BootloaderInterface;
use IfCastle\Application\EngineInterface;
use IfCastle\Async\CoroutineContextInterface;
use IfCastle\Async\CoroutineSchedulerInterface;
use IfCastle\DI\ContainerBuilder;
use IfCastle\TrueAsync\Bootloader as TrueAsyncBootloader;
use IfCastle\TrueAsync\CoroutineContext;
use IfCastle\TrueAsync\CoroutineScheduler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BootloaderTest extends TestCase
{
    /**
     * @return array<string, array{list<class-string<BootloaderInterface>>}>
     */
    public static function orders(): array
    {
        return [
            'trueasync engine first'    => [[TrueAsyncBootloader::class, Bootloader::class]],
            'web server first'          => [[Bootloader::class, TrueAsyncBootloader::class]],
        ];
    }

    /**
     * @param list<class-string<BootloaderInterface>> $bootloaders
     */
    #[DataProvider('orders')]
    public function testBindsTheWebServerEngineWhateverTheOrder(array $bootloaders): void
    {
        $builder                    = new ContainerBuilder();

        foreach ($bootloaders as $bootloader) {
            new $bootloader()->buildBootloader($this->executorFor($builder));
        }

        $this->assertSame(WebServerEngine::class, $builder->get(EngineInterface::class)->getClassName());
        $this->assertSame(CoroutineContext::class, $builder->get(CoroutineContextInterface::class)->getClassName());
        $this->assertSame(CoroutineScheduler::class, $builder->get(CoroutineSchedulerInterface::class)->getClassName());
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
