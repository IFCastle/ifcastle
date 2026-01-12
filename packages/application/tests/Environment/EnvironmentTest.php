<?php

declare(strict_types=1);

namespace IfCastle\Application\Environment;

use IfCastle\DI\Resolver;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    private Environment $environment;

    #[\Override]
    protected function setUp(): void
    {
        $this->environment           = new Environment(new Resolver(), ['key' => 'value']);
    }

    public function testGet(): void
    {
        $this->assertEquals('value', $this->environment->get('key'));
    }

    public function testSet(): void
    {
        $this->environment->set('key2', 'value2');
        $this->assertEquals('value2', $this->environment->get('key2'));
    }

    public function testDelete(): void
    {
        $this->environment->delete('key');
        $this->assertNull($this->environment->get('key'));
    }

    public function testIsExist(): void
    {
        $this->assertTrue($this->environment->isExist('key'));
        $this->assertFalse($this->environment->isExist('key2'));
    }

    public function testFind(): void
    {
        $this->assertEquals('value', $this->environment->find('key'));
        $this->assertNull($this->environment->find('key2'));
    }

    public function testIs(): void
    {
        $this->assertTrue($this->environment->is('key'));
        $this->assertFalse($this->environment->is('key2'));
    }
}
