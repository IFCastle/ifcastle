<?php

declare(strict_types=1);

namespace IfCastle\Configurator\Toml;

use PHPUnit\Framework\TestCase;

class ConfigMainAppenderTest extends TestCase
{
    private string $testDir;
    private string $testFile;

    #[\Override]
    protected function setUp(): void
    {
        $this->testDir = \sys_get_temp_dir() . '/config_test_' . \uniqid();
        \mkdir($this->testDir);
        $this->testFile = $this->testDir . '/main.toml';
        \file_put_contents($this->testFile, '');
    }

    #[\Override]
    protected function tearDown(): void
    {
        if (\file_exists($this->testFile)) {
            \unlink($this->testFile);
        }
        if (\is_dir($this->testDir)) {
            \rmdir($this->testDir);
        }
    }

    public function testAppendSectionIfNotExists(): void
    {
        $config                     = new ConfigMainAppender($this->testDir);
        $config->appendSectionIfNotExists('main', [
            'foo' => 'bar',
            'baz' => 'qux',
        ], "My comment\nMy comment 2");

        $this->assertFileExists($this->testFile);
        $expected                   = <<<TOML

            # ================================================
            # My comment
            # My comment 2
            # ================================================
            [main]
            foo = "bar"
            baz = "qux"
            TOML;

        $this->assertEquals(
            \str_replace(["\r\n", "\r"], "\n", $expected),
            \str_replace(["\r\n", "\r"], "\n", \file_get_contents($this->testFile))
        );
    }
}
