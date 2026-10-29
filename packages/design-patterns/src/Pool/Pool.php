<?php

declare(strict_types=1);

namespace IfCastle\DesignPatterns\Pool;

use Async\Future;
use Async\FutureState;
use Async\OperationCanceledException;
use IfCastle\DesignPatterns\Factory\FactoryInterface;
use IfCastle\DI\DisposableInterface;

use function Async\await;
use function Async\timeout;

/**
 * @template T of object
 * @implements PoolInterface<T>
 */
class Pool implements PoolInterface
{
    /**
     * List of decorated objects<T>.
     * @var T[]
     */
    private array $borrowed         = [];

    private int   $lastBorrowAt     = 0;

    /**
     * Borrowers waiting for a free slot, by spl_object_id, oldest first; return() wakes the oldest.
     * @var array<int, FutureState>
     */
    private array $waiters          = [];

    /**
     * @param FactoryInterface<T>       $factory
     * @param int                       $timeout milliseconds a borrow over $maxPoolSize waits for an object to come
     *                                           back before it gives null; zero or less gives null at once. Waiting
     *                                           needs TrueAsync: without it a borrow over the limit never waits.
     * @param StackInterface<T>         $stack
     * @param ReturnFactoryInterface<T> $returnFactory
     */
    public function __construct(
        protected FactoryInterface          $factory,
        protected int                       $maxPoolSize,
        protected int                       $minPoolSize        = 0,
        protected int                       $timeout            = -1,
        protected int                       $delayPoolReduction = 0,
        protected StackInterface            $stack              = new Stack(),
        protected ReturnFactoryInterface    $returnFactory      = new ReturnFactory()
    ) {}

    protected function init(): void
    {
        for ($i = 0; $i < $this->minPoolSize; $i++) {
            $this->stack->push($this->factory->createObject());
        }
    }

    #[\Override]
    public function borrow(): object|null
    {
        if (\count($this->borrowed) >= $this->maxPoolSize && false === $this->waitForFreeSlot()) {
            return null;
        }

        if ($this->stack->getSize() === 0) {
            $this->init();
        }

        if ($this->stack->getSize() > 0) {
            $originalObject         = $this->stack->pop();
            $decorator              = $this->returnFactory->createDecorator($originalObject, $this);
            $this->borrowed[\spl_object_id($decorator)] = $decorator;

            if ($this->delayPoolReduction > 0) {
                $this->lastBorrowAt = \time();
            }

            return $decorator;
        }

        $decorator               = $this->returnFactory->createDecorator($this->factory->createObject(), $this);
        $this->borrowed[\spl_object_id($decorator)] = $decorator;

        if ($this->delayPoolReduction > 0) {
            $this->lastBorrowAt = \time();
        }

        return $decorator;
    }

    #[\Override]
    public function return(object $object): void
    {
        if (false === \array_key_exists(\spl_object_id($object), $this->borrowed)) {

            if ($object instanceof DisposableInterface) {
                $object->dispose();
            }

            return;
        }

        unset($this->borrowed[\spl_object_id($object)]);
        $this->wakeWaiter();

        $originalObject             = $object->getOriginalObject();

        if ($object instanceof DisposableInterface) {
            $object->dispose();
        }

        //
        // Reduction algorithm:
        // We don't reduce the pool size if the delay time has not expired.
        // This is necessary to avoid frequent creation and destruction of objects.
        //
        $isReduceTimeout            = $this->lastBorrowAt === 0
                                      || (\time() - $this->lastBorrowAt) > $this->delayPoolReduction;

        if ($this->stack->getSize() + 1 > $this->minPoolSize && $isReduceTimeout) {
            return;
        }

        if ($originalObject !== null) {
            $this->stack->push($originalObject);
        }
    }

    #[\Override]
    public function rebuild(): void
    {
        $this->stack->clear();
        $this->borrowed             = [];

        while ($this->waiters !== []) {
            $this->wakeWaiter();
        }
    }

    /**
     * Suspends the current coroutine until a slot is free or the timeout runs out.
     *
     * @return bool true when a slot is free
     */
    private function waitForFreeSlot(): bool
    {
        if ($this->timeout <= 0 || false === \class_exists(FutureState::class)) {
            return false;
        }

        $deadline                   = \hrtime(true) + $this->timeout * 1_000_000;

        // A woken borrower checks again: another coroutine may have taken the freed slot before it resumed.
        while (\count($this->borrowed) >= $this->maxPoolSize) {
            $left                   = \intdiv($deadline - \hrtime(true), 1_000_000);

            if ($left <= 0) {
                return false;
            }

            $waiter                 = new FutureState();
            $this->waiters[\spl_object_id($waiter)] = $waiter;

            try {
                await(new Future($waiter), timeout($left));
            } catch (OperationCanceledException) {
                // The timeout ran out (a cancel of the coroutine itself is an AsyncCancellation and goes up). A
                // return() may have woken this waiter in the same tick: its slot is taken here rather than lost.
                return \count($this->borrowed) < $this->maxPoolSize;
            } finally {
                unset($this->waiters[\spl_object_id($waiter)]);
            }
        }

        return true;
    }

    private function wakeWaiter(): void
    {
        $id                         = \array_key_first($this->waiters);

        if ($id === null) {
            return;
        }

        $waiter                     = $this->waiters[$id];
        unset($this->waiters[$id]);
        $waiter->complete(true);
    }

    #[\Override]
    public function getMaxPoolSize(): int
    {
        return $this->maxPoolSize;
    }

    #[\Override]
    public function getMinPoolSize(): int
    {
        return $this->minPoolSize;
    }

    #[\Override]
    public function getMaxWaitTimeout(): int
    {
        return $this->timeout;
    }

    #[\Override]
    public function getUsed(): int
    {
        return \count($this->borrowed);
    }

    #[\Override]
    public function getPoolSize(): int
    {
        return $this->stack->getSize();
    }
}
