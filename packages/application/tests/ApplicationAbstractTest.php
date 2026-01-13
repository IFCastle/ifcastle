<?php

declare(strict_types=1);

namespace IfCastle\Application;

use IfCastle\Application\Bootloader\Builder\BootloaderBuilderInMemory;
use IfCastle\Application\MockApplication\TestBootloader;
use PHPUnit\Framework\TestCase;

class ApplicationAbstractTest extends TestCase
{
    public const string APP_DIR     = __DIR__ . '/MockApplication';

    #[\Override]
    protected function setUp(): void
    {
        $logFile                    = self::APP_DIR . '/logs/critical.log';

        if (\file_exists($logFile)) {
            \unlink($logFile);
            \unlink(self::APP_DIR . '/logs');
        }
    }

    public function testRun(): void
    {
        $bootloader                 = new BootloaderBuilderInMemory(
            self::APP_DIR,
            'test',
            [
                'some-tag',
            ],
            [
                TestBootloader::class,
            ],
            [

            ]
        );

        new Runner(self::APP_DIR, 'test', TestApplication::class)
            ->defineBootloaderBuilder($bootloader)
            ->runAndDispose();

        $this->assertLogFileNotExist();
    }

    protected function assertLogFileNotExist(): void
    {
        $this->assertFileDoesNotExist(self::APP_DIR . '/logs/critical.log');
    }
}
