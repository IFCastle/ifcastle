<?php

declare(strict_types=1);

namespace IfCastle\DesignPatterns\Pool;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

use function Async\await;
use function Async\delay;
use function Async\spawn;

class PoolTest extends TestCase
{
    public function testBorrow(): void
    {
        $pool                       = new Pool(new SomeFactory(), 3);
        $object                     = $pool->borrow();

        $this->assertInstanceOf(DecoratorInterface::class, $object);
        $this->assertInstanceOf(SomeObject::class, $object->getOriginalObject());
        $this->assertEquals('object1', $object->getOriginalObject()->name);
    }

    public function testReturn(): void
    {
        $pool                       = new Pool(new SomeFactory(), 3);
        $object                     = $pool->borrow();

        $this->assertInstanceOf(DecoratorInterface::class, $object);
        $this->assertInstanceOf(SomeObject::class, $object->getOriginalObject());
        $this->assertEquals('object1', $object->getOriginalObject()->name);

        $pool->return($object);

        $this->assertNull($object->getOriginalObject());

        $this->assertEquals(0, $pool->getUsed(), 'Pool should have no used objects');
    }

    public function testBorrowAll(): void
    {
        $pool                       = new Pool(new SomeFactory(), 3);

        $object1                    = $pool->borrow();
        $object2                    = $pool->borrow();
        $object3                    = $pool->borrow();

        $this->assertInstanceOf(DecoratorInterface::class, $object1);
        $this->assertInstanceOf(DecoratorInterface::class, $object2);
        $this->assertInstanceOf(DecoratorInterface::class, $object3);

        $this->assertInstanceOf(SomeObject::class, $object1->getOriginalObject());
        $this->assertInstanceOf(SomeObject::class, $object2->getOriginalObject());
        $this->assertInstanceOf(SomeObject::class, $object3->getOriginalObject());

        $this->assertEquals('object1', $object1->getOriginalObject()->name);
        $this->assertEquals('object2', $object2->getOriginalObject()->name);
        $this->assertEquals('object3', $object3->getOriginalObject()->name);

        $pool->return($object1);
        $pool->return($object2);
        $pool->return($object3);

        $this->assertNull($object1->getOriginalObject());
        $this->assertNull($object2->getOriginalObject());
        $this->assertNull($object3->getOriginalObject());

        $this->assertEquals(0, $pool->getUsed(), 'Pool should have no used objects');
    }

    public function testRebuild(): void
    {
        $pool = new Pool(new SomeFactory(), 3);

        $object1 = $pool->borrow();
        $object2 = $pool->borrow();
        $object3 = $pool->borrow();

        $this->assertInstanceOf(DecoratorInterface::class, $object1);
        $this->assertInstanceOf(DecoratorInterface::class, $object2);
        $this->assertInstanceOf(DecoratorInterface::class, $object3);

        $this->assertInstanceOf(SomeObject::class, $object1->getOriginalObject());
        $this->assertInstanceOf(SomeObject::class, $object2->getOriginalObject());
        $this->assertInstanceOf(SomeObject::class, $object3->getOriginalObject());

        $this->assertEquals('object1', $object1->getOriginalObject()->name);
        $this->assertEquals('object2', $object2->getOriginalObject()->name);
        $this->assertEquals('object3', $object3->getOriginalObject()->name);

        $pool->return($object1);
        $pool->return($object2);
        $pool->return($object3);

        $this->assertNull($object1->getOriginalObject());
        $this->assertNull($object2->getOriginalObject());
        $this->assertNull($object3->getOriginalObject());

        $this->assertEquals(0, $pool->getUsed(), 'Pool should have no used objects');

        $pool->rebuild();

        $object1 = $pool->borrow();
        $object2 = $pool->borrow();
        $object3 = $pool->borrow();

        $this->assertInstanceOf(DecoratorInterface::class, $object1);
        $this->assertInstanceOf(DecoratorInterface::class, $object2);
        $this->assertInstanceOf(DecoratorInterface::class, $object3);

        $this->assertInstanceOf(SomeObject::class, $object1->getOriginalObject());
        $this->assertInstanceOf(SomeObject::class, $object2->getOriginalObject());
        $this->assertInstanceOf(SomeObject::class, $object3->getOriginalObject());

        $this->assertEquals('object4', $object1->getOriginalObject()->name);
        $this->assertEquals('object5', $object2->getOriginalObject()->name);
    }

    public function testReturnWithinReductionDelayKeepsCreatedObject(): void
    {
        $pool                       = new Pool(new SomeFactory(), 3, delayPoolReduction: 60);
        $object                     = $pool->borrow();
        $pool->return($object);

        $this->assertEquals(1, $pool->getPoolSize(), 'An object returned within the delay stays in the pool');
        $this->assertEquals('object1', $pool->borrow()->getOriginalObject()->name);
    }

    #[RequiresPhpExtension('true_async')]
    public function testBorrowOverTheLimitWaitsForAReturnedObject(): void
    {
        $pool                       = new Pool(new SomeFactory(), 1, timeout: 1000);
        $held                       = $pool->borrow();
        $events                     = [];

        $waiter                     = spawn(static function () use ($pool, &$events): object|null {
            $events[]               = 'borrow';
            $object                 = $pool->borrow();
            $events[]               = 'borrowed';

            return $object;
        });

        delay(20);
        $events[]                   = 'return';
        $pool->return($held);
        $object                     = await($waiter);

        $this->assertSame(['borrow', 'return', 'borrowed'], $events);
        $this->assertInstanceOf(DecoratorInterface::class, $object);
        $this->assertSame(1, $pool->getUsed());
    }

    #[RequiresPhpExtension('true_async')]
    public function testBorrowOverTheLimitGivesNullWhenTheTimeoutExpires(): void
    {
        $pool                       = new Pool(new SomeFactory(), 1, timeout: 50);
        $held                       = $pool->borrow();
        $startedAt                  = \hrtime(true);

        $object                     = await(spawn(static fn(): object|null => $pool->borrow()));

        $this->assertNull($object);
        // The event loop's timers count from its cached clock and may fire a millisecond or two early.
        $this->assertGreaterThanOrEqual(45, \intdiv(\hrtime(true) - $startedAt, 1_000_000));
        $this->assertSame(1, $pool->getUsed());

        $pool->return($held);
        $this->assertInstanceOf(DecoratorInterface::class, $pool->borrow(), 'a waiter that gave up holds no slot');
    }

    public function testBorrowOverTheLimitWithoutTimeoutGivesNullAtOnce(): void
    {
        $pool                       = new Pool(new SomeFactory(), 1);
        $pool->borrow();

        $this->assertNull($pool->borrow());
    }
}
