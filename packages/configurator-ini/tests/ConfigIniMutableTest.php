<?php

declare(strict_types=1);

namespace IfCastle\Configurator;

use PHPUnit\Framework\TestCase;

class ConfigIniMutableTest extends TestCase
{
    private string $testFile;

    #[\Override]
    protected function setUp(): void
    {
        $this->testFile = \sys_get_temp_dir() . '/test_' . \uniqid() . '.ini';
        \file_put_contents($this->testFile, '');
    }

    #[\Override]
    protected function tearDown(): void
    {
        if (\file_exists($this->testFile)) {
            \unlink($this->testFile);
        }
    }


    public function testSave(): void
    {
        $config                     = new ConfigIniMutable($this->testFile);

        $config->set('foo', 'bar');
        $config->set('baz', 'qux');
        $config->save();

        $this->assertFileExists($this->testFile);
        $expected                   = <<<INI
            foo = "bar"
            baz = "qux"
            INI;

        $this->assertEquals(
            \str_replace(["\r\n", "\r"], "\n", $expected),
            \str_replace(["\r\n", "\r"], "\n", \file_get_contents($this->testFile))
        );
    }

    public function testSaveNested(): void
    {
        $config                     = new ConfigIniMutable($this->testFile);

        $config->set('foo.bar', 'baz');
        $config->set('foo.qux', 'quux');
        $config->setSection('foo.nested.section', [
            'key' => 'value',
            'list' => ['item1', 'item2'],
        ]);

        $config->save();

        $this->assertFileExists($this->testFile);
        $expected                   = <<<INI

            ;----------------------------------------
            [foo]
            bar = "baz"
            qux = "quux"

            ;----------------------------------------
            [foo.nested.section]
            key = "value"
            list[] = "item1"
            list[] = "item2"
            INI;

        $this->assertEquals(
            \str_replace(["\r\n", "\r"], "\n", $expected),
            \str_replace(["\r\n", "\r"], "\n", \file_get_contents($this->testFile))
        );
    }
}
