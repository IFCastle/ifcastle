<?php

declare(strict_types=1);

namespace IfCastle\Application;

use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase
{
    private string $appDir;

    #[\Override]
    protected function setUp(): void
    {
        $this->appDir               = \sys_get_temp_dir() . '/ifcastle-runner-' . \bin2hex(\random_bytes(6));

        // ApplicationAbstract refuses to start without a vendor directory.
        \mkdir($this->appDir . '/vendor', 0o777, true);
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach (['/logs/critical.log', '/logs', '/vendor', ''] as $path) {
            $path                   = $this->appDir . $path;

            if (\is_file($path)) {
                \unlink($path);
            } elseif (\is_dir($path)) {
                \rmdir($path);
            }
        }
    }

    public function testRunAndExitFailsWhenTheEngineDoesNotStart(): void
    {
        \exec(
            \PHP_BINARY . ' ' . \escapeshellarg(__DIR__ . '/run-and-exit.php') . ' ' . \escapeshellarg($this->appDir) . ' 2>&1',
            $output,
            $exitCode
        );

        $this->assertSame(1, $exitCode, \implode("\n", $output));
    }
}
