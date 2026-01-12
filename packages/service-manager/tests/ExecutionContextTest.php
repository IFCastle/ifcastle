<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use PHPUnit\Framework\TestCase;

class ExecutionContextTest extends TestCase
{
    public function testToArray(): void
    {
        $executionContext = new ExecutionContext([
            'foo' => 'bar',
            'baz' => 'qux',
            'recursive' => [
                'foo' => 'bar',
                'baz' => 'qux',
                'recursive' => [
                    'foo' => 'bar',
                    'baz' => 'qux',
                ],
            ],
        ]);

        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'qux',
            'recursive' => [
                'foo' => 'bar',
                'baz' => 'qux',
                'recursive' => [
                    'foo' => 'bar',
                    'baz' => 'qux',
                ],
            ],
        ], $executionContext->toArray());
    }
}
