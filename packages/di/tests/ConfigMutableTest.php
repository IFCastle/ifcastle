<?php

declare(strict_types=1);

namespace IfCastle\DI;

use PHPUnit\Framework\TestCase;

class ConfigMutableTest extends TestCase
{
    public function testRemoveNestedKey(): void
    {
        $config                     = new ConfigMutable(['foo' => ['bar' => 'baz', 'qux' => 'quux']]);

        $config->remove('foo', 'bar');

        $this->assertSame(['qux' => 'quux'], $config->findSection('foo'));
    }

    public function testRemoveMissingKeyChangesNothing(): void
    {
        $config                     = new ConfigMutable(['foo' => ['bar' => 'baz']]);

        $config->remove('foo', 'absent');
        $config->remove('foo', 'bar', 'deeper');
        $config->remove();

        $this->assertSame(['bar' => 'baz'], $config->findSection('foo'));
    }
}
