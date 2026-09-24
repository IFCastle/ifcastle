<?php

declare(strict_types=1);

namespace IfCastle\TrueAsync;

use IfCastle\Exceptions\UnexpectedValue;
use IfCastle\TrueAsync\Exceptions\UnsupportedOperation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

use function Async\await;
use function Async\delay;
use function Async\spawn;
use function Async\timeout;

class CoroutineSchedulerTest extends TestCase
{
    public function testRunExecutesTheClosure(): void
    {
        $scheduler                  = new CoroutineScheduler();
        $executed                   = false;

        $coroutine                  = $scheduler->run(function () use (&$executed): void {
            $executed               = true;
        });

        Support::waitUntilFinished($coroutine);

        $this->assertTrue($executed);
        $this->assertFalse($coroutine->isCancelled());
    }

    public function testErrorIsLoggedAndSiblingsKeepRunning(): void
    {
        $logger                     = Support::collectingLogger();

        $scheduler                  = new CoroutineScheduler($logger);
        $siblingFinished            = false;

        $failing                    = $scheduler->run(static function (): void {
            throw new \RuntimeException('boom');
        });

        $sibling                    = $scheduler->run(function () use (&$siblingFinished): void {
            delay(5);
            $siblingFinished        = true;
        });

        Support::waitUntilFinished($failing);
        Support::waitUntilFinished($sibling);

        $this->assertSame(['error: boom'], $logger->messages);
        $this->assertTrue($siblingFinished);
    }

    public function testDelayRunsCallbackAfterDelay(): void
    {
        $scheduler                  = new CoroutineScheduler();
        $calledAt                   = null;
        $startedAt                  = \hrtime(true);

        $scheduler->delay(0.02, static function () use (&$calledAt): void {
            $calledAt               = \hrtime(true);
        });

        delay(50);

        $this->assertNotNull($calledAt);
        $this->assertGreaterThanOrEqual(20_000_000, $calledAt - $startedAt);
    }

    public function testCancelIntervalStopsTheCallbacks(): void
    {
        $scheduler                  = new CoroutineScheduler();
        $ticks                      = 0;

        $id                         = $scheduler->interval(0.005, static function () use (&$ticks): void {
            $ticks++;
        });

        delay(30);
        $scheduler->cancelInterval($id);
        $ticksAtCancel              = $ticks;
        delay(30);

        $this->assertGreaterThan(0, $ticksAtCancel);
        $this->assertSame($ticksAtCancel, $ticks);
    }

    public function testIntervalShorterThanOneMillisecondIsRejected(): void
    {
        $this->expectException(UnexpectedValue::class);

        new CoroutineScheduler()->interval(0.0001, static fn() => null);
    }

    public function testCancelIntervalIgnoresUnknownId(): void
    {
        new CoroutineScheduler()->cancelInterval(-42);

        $this->addToAssertionCount(1);
    }

    public function testStopAllCoroutinesCancelsSuspendedCoroutines(): void
    {
        $scheduler                  = new CoroutineScheduler();
        $reason                     = new \RuntimeException('shutdown');
        $caught                     = null;

        $coroutine                  = $scheduler->run(function () use (&$caught): void {
            try {
                delay(1000);
            } catch (\Cancellation $cancellation) {
                $caught             = $cancellation;
                throw $cancellation;
            }
        });

        delay(5);
        $this->assertTrue($scheduler->stopAllCoroutines($reason));
        Support::waitUntilFinished($coroutine);

        $this->assertTrue($coroutine->isCancelled());
        $this->assertInstanceOf(\IfCastle\Async\CancelledExceptionInterface::class, $caught);
        $this->assertSame($reason, $caught->getPrevious());
    }

    public function testStopOnCompletedCoroutineReturnsFalse(): void
    {
        $coroutine                  = new CoroutineScheduler()->run(static fn() => null);
        Support::waitUntilFinished($coroutine);

        $this->assertFalse($coroutine->stop());
    }

    /**
     * @return array<string, array{\Closure(CoroutineScheduler): mixed}>
     */
    public static function unsupportedMembers(): array
    {
        return [
            'await'                      => [static fn(CoroutineScheduler $s) => $s->await([])],
            'awaitFirst'                 => [static fn(CoroutineScheduler $s) => $s->awaitFirst([])],
            'awaitFirstSuccessful'       => [static fn(CoroutineScheduler $s) => $s->awaitFirstSuccessful([])],
            'awaitAll'                   => [static fn(CoroutineScheduler $s) => $s->awaitAll([])],
            'awaitAnyN'                  => [static fn(CoroutineScheduler $s) => $s->awaitAnyN(1, [])],
            'createChannelPair'          => [static fn(CoroutineScheduler $s) => $s->createChannelPair()],
            'createQueue'                => [static fn(CoroutineScheduler $s) => $s->createQueue()],
            'createTimeoutCancellation'  => [static fn(CoroutineScheduler $s) => $s->createTimeoutCancellation(1.0)],
            'compositeCancellation'      => [static fn(CoroutineScheduler $s) => $s->compositeCancellation()],
            'createDeferredCancellation' => [static fn(CoroutineScheduler $s) => $s->createDeferredCancellation()],
        ];
    }

    #[DataProvider('unsupportedMembers')]
    public function testUnsupportedMemberThrows(\Closure $call): void
    {
        $this->expectException(UnsupportedOperation::class);

        $call(new CoroutineScheduler());
    }

    public function testFinishedTimerIdDoesNotCancelLaterTimers(): void
    {
        $scheduler                  = new CoroutineScheduler();
        $staleId                    = $scheduler->delay(0, static fn() => null);
        delay(5);

        $fired                      = 0;

        for ($i = 0; $i < 20; $i++) {
            $scheduler->delay(0.01, static function () use (&$fired): void {
                $fired++;
            });
        }

        $scheduler->cancelInterval($staleId);
        delay(40);

        $this->assertSame(20, $fired);
    }

    public function testCancelIntervalCancelsDelayBeforeItFires(): void
    {
        $scheduler                  = new CoroutineScheduler();
        $fired                      = false;

        $id                         = $scheduler->delay(0.01, static function () use (&$fired): void {
            $fired                  = true;
        });

        $scheduler->cancelInterval($id);
        delay(30);

        $this->assertFalse($fired);
    }

    public function testDeferRunsTheCallback(): void
    {
        $scheduler                  = new CoroutineScheduler();
        $called                     = false;

        $scheduler->defer(static function () use (&$called): void {
            $called                 = true;
        });

        $this->assertFalse($called);
        delay(5);
        $this->assertTrue($called);
    }

    public function testStopAllCoroutinesCancelsTimers(): void
    {
        $scheduler                  = new CoroutineScheduler();
        $fired                      = false;

        $scheduler->delay(0.01, static function () use (&$fired): void {
            $fired                  = true;
        });
        $scheduler->interval(0.005, static function () use (&$fired): void {
            $fired                  = true;
        });

        $scheduler->stopAllCoroutines();
        delay(30);

        $this->assertFalse($fired);
    }

    public function testIntervalKeepsTickingAfterCallbackError(): void
    {
        $scheduler                  = new CoroutineScheduler(Support::collectingLogger());
        $ticks                      = 0;

        $id                         = $scheduler->interval(0.002, static function () use (&$ticks): void {
            $ticks++;
            throw new \RuntimeException('tick failed');
        });

        delay(30);
        $scheduler->cancelInterval($id);

        $this->assertGreaterThan(1, $ticks);
    }

    public function testFiredTokenInsideRunIsReported(): void
    {
        $logger                     = Support::collectingLogger();
        $scheduler                  = new CoroutineScheduler($logger);

        $coroutine                  = $scheduler->run(static function (): void {
            await(spawn(static fn() => delay(1000)), timeout(5));
        });

        Support::waitUntilFinished($coroutine);

        $this->assertCount(1, $logger->messages);
        $this->assertFalse($coroutine->isCancelled());
    }

    public function testFailingLoggerDoesNotStopSiblings(): void
    {
        $logger                     = new class extends AbstractLogger {
            public function log($level, \Stringable|string $message, array $context = []): void
            {
                throw new \LogicException('logger down');
            }
        };

        $scheduler                  = new CoroutineScheduler($logger);
        $siblingFinished            = false;
        $errorLog                   = \ini_set('error_log', \tempnam(\sys_get_temp_dir(), 'trueasync-test'));

        try {
            $failing                = $scheduler->run(static function (): void {
                throw new \RuntimeException('boom');
            });
            $sibling                = $scheduler->run(static function () use (&$siblingFinished): void {
                delay(5);
                $siblingFinished    = true;
            });

            Support::waitUntilFinished($failing);
            Support::waitUntilFinished($sibling);
        } finally {
            \unlink((string) \ini_get('error_log'));
            \ini_set('error_log', (string) $errorLog);
        }

        $this->assertTrue($siblingFinished);
    }

    public function testStopBeforeStartCancelsWithoutRunningOrLogging(): void
    {
        $logger                     = Support::collectingLogger();
        $scheduler                  = new CoroutineScheduler($logger);
        $executed                   = false;

        $coroutine                  = $scheduler->run(static function () use (&$executed): void {
            $executed               = true;
        });

        $this->assertTrue($coroutine->stop());
        Support::waitUntilFinished($coroutine);

        $this->assertFalse($executed);
        $this->assertTrue($coroutine->isCancelled());
        $this->assertSame([], $logger->messages);
    }
}
