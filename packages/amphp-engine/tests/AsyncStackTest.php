<?php

declare(strict_types=1);

namespace IfCastle\Amphp;

use PHPUnit\Framework\TestCase;

class AsyncStackTest extends TestCase
{
    public function testPushAndPop(): void
    {
        $object1                    = new \stdClass();
        $object2                    = new \stdClass();

        $stack                      = new AsyncStack();
        $stack->push($object1);
        $stack->push($object2);

        $this->assertSame($object1, $stack->pop());
        $this->assertSame($object2, $stack->pop());
    }

    public function testClear(): void
    {
        $object1                    = new \stdClass();
        $object2                    = new \stdClass();

        $stack                      = new AsyncStack();
        $stack->push($object1);
        $stack->push($object2);

        $stack->clear();

        $this->assertNull($stack->pop());
        $this->assertNull($stack->pop());
    }

    public function testGetSize(): void
    {
        $object1                    = new \stdClass();
        $object2                    = new \stdClass();

        $stack                      = new AsyncStack();
        $stack->push($object1);
        $stack->push($object2);

        $this->assertSame(2, $stack->getSize());
    }

    public function testPopEmpty(): void
    {
        $stack                      = new AsyncStack();
        $this->assertNull($stack->pop());
    }
}
