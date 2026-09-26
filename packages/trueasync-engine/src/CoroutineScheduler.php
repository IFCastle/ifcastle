<?php

declare(strict_types=1);

namespace IfCastle\TrueAsync;

use Async\Coroutine;
use Async\Scope;
use IfCastle\Async\CancellationInterface;
use IfCastle\Async\CoroutineInterface;
use IfCastle\Async\CoroutineSchedulerInterface;
use IfCastle\Async\DeferredCancellationInterface;
use IfCastle\Async\QueueInterface;
use IfCastle\Exceptions\UnexpectedValue;
use IfCastle\TrueAsync\Exceptions\CancelledException;
use IfCastle\TrueAsync\Exceptions\UnsupportedOperation;
use IfCastle\TrueAsync\Internal\ErrorReporter;
use IfCastle\TrueAsync\Internal\ParentLink;
use Psr\Log\LoggerInterface;

use function Async\coroutine_context;
use function Async\current_coroutine;
use function Async\delay;
use function Async\spawn;

/**
 * Starts and stops coroutines on TrueAsync.
 *
 * run() starts a coroutine in the caller's Scope that inherits the caller's coroutine context
 * (see CoroutineContext). defer(), delay() and interval() start timer coroutines in a Scope owned
 * by the scheduler, without that inheritance: a timer outlives the request that created it and
 * must not keep the request or its values alive.
 *
 * An error thrown by a callback is logged and ends only that callback; an interval keeps ticking.
 *
 * The await*, channel, queue and cancellation members throw UnsupportedOperation: they mirror the
 * AMPHP API, nothing in IfCastle calls them, and code that needs them uses Async\* directly.
 */
final class CoroutineScheduler implements CoroutineSchedulerInterface
{
    /**
     * Coroutines started by run() and not yet completed, by coroutine id.
     *
     * @var array<int, Coroutine>
     */
    private array $coroutines       = [];

    /**
     * Timer coroutines not yet completed, by timer id.
     *
     * @var array<int, Coroutine>
     */
    private array $timers           = [];

    /**
     * Timer ids come from this counter, never from coroutine ids: those are object handles, which
     * PHP may give to a new object once a coroutine is freed, so a stale id could cancel another one.
     */
    private int $lastTimerId        = 0;

    private ?Scope $timerScope      = null;

    private readonly ErrorReporter $errorReporter;

    /**
     * @param LoggerInterface|null $logger Receives errors thrown by callbacks; without it they go to error_log().
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->errorReporter        = new ErrorReporter($logger);
    }

    #[\Override]
    public function run(\Closure $function): CoroutineInterface
    {
        // coroutine_context() starts the scheduler if needed, so current_coroutine() cannot fail after it.
        $link                       = new ParentLink(coroutine_context(), current_coroutine()->getId());

        $coroutine                  = spawn(function () use ($function, $link): void {
            coroutine_context()->set(ParentLink::key(), $link);
            $this->errorReporter->call($function);
        });

        $id                         = $coroutine->getId();
        $this->coroutines[$id]      = $coroutine;
        $coroutine->finally(function () use ($id): void {
            unset($this->coroutines[$id]);
        });

        return new CoroutineAdapter($coroutine);
    }

    #[\Override]
    public function defer(callable $callback): void
    {
        $this->startTimer(function () use ($callback): void {
            $this->errorReporter->call($callback);
        });
    }

    /**
     * @param float|int $delay Seconds; rounded to whole milliseconds, negative means now.
     *
     * @return int Timer id to pass to cancelInterval() to cancel the call before it happens.
     */
    #[\Override]
    public function delay(float|int $delay, callable $callback): int
    {
        $milliseconds               = \max(0, self::toMilliseconds($delay));

        return $this->startTimer(function () use ($milliseconds, $callback): void {
            delay($milliseconds);
            $this->errorReporter->call($callback);
        });
    }

    /**
     * Calls the callback every interval, measured from the end of the previous call.
     *
     * @param float|int $interval Seconds, at least one millisecond.
     *
     * @return int Timer id to pass to cancelInterval().
     *
     * @throws UnexpectedValue when the interval is shorter than one millisecond.
     */
    #[\Override]
    public function interval(float|int $interval, callable $callback): int
    {
        $milliseconds               = self::toMilliseconds($interval);

        if ($milliseconds < 1) {
            throw new UnexpectedValue('$interval', $interval, 'at least 0.001 seconds');
        }

        return $this->startTimer(function () use ($milliseconds, $callback): void {
            // cancelInterval() and stopAllCoroutines() end the loop: the cancellation is thrown from delay().
            while (false === current_coroutine()->isCancellationRequested()) {
                delay($milliseconds);
                $this->errorReporter->call($callback);
            }
        });
    }

    /**
     * Cancels a timer started by defer(), delay() or interval(); an unknown or finished id is ignored.
     */
    #[\Override]
    public function cancelInterval(int|string $timerId): void
    {
        ($this->timers[$timerId] ?? null)?->cancel(new CancelledException('Timer cancelled'));
    }

    /**
     * Cancels every coroutine started by run() and every timer that has not completed, except the
     * calling coroutine, which keeps running. Coroutines started by TrueAsync itself (HTTP request
     * handlers, direct Async\spawn()) are not affected.
     */
    #[\Override]
    public function stopAllCoroutines(?\Throwable $exception = null): bool
    {
        // The caller may be one of them: it stops the others and keeps running.
        $caller                     = current_coroutine();

        foreach ([...$this->coroutines, ...$this->timers] as $coroutine) {
            if ($coroutine !== $caller) {
                $coroutine->cancel(CoroutineAdapter::toCancellation($exception));
            }
        }

        return true;
    }

    #[\Override]
    public function await(iterable $futures, ?CancellationInterface $cancellation = null): array
    {
        throw new UnsupportedOperation(__METHOD__);
    }

    #[\Override]
    public function awaitFirst(iterable $futures, ?CancellationInterface $cancellation = null): mixed
    {
        throw new UnsupportedOperation(__METHOD__);
    }

    #[\Override]
    public function awaitFirstSuccessful(iterable $futures, ?CancellationInterface $cancellation = null): mixed
    {
        throw new UnsupportedOperation(__METHOD__);
    }

    #[\Override]
    public function awaitAll(iterable $futures, ?CancellationInterface $cancellation = null): array
    {
        throw new UnsupportedOperation(__METHOD__);
    }

    #[\Override]
    public function awaitAnyN(int $count, iterable $futures, ?CancellationInterface $cancellation = null): array
    {
        throw new UnsupportedOperation(__METHOD__);
    }

    #[\Override]
    public function createChannelPair(int $size = 0): array
    {
        throw new UnsupportedOperation(__METHOD__);
    }

    #[\Override]
    public function createQueue(int $size = 0): QueueInterface
    {
        throw new UnsupportedOperation(__METHOD__);
    }

    #[\Override]
    public function createTimeoutCancellation(float $timeout, string $message = 'Operation timed out'): CancellationInterface
    {
        throw new UnsupportedOperation(__METHOD__);
    }

    #[\Override]
    public function compositeCancellation(CancellationInterface ...$cancellations): CancellationInterface
    {
        throw new UnsupportedOperation(__METHOD__);
    }

    #[\Override]
    public function createDeferredCancellation(): DeferredCancellationInterface
    {
        throw new UnsupportedOperation(__METHOD__);
    }

    private function startTimer(\Closure $body): int
    {
        $this->timerScope           ??= new Scope();

        $timerId                    = ++$this->lastTimerId;
        $this->timers[$timerId]     = $this->timerScope->spawn($body);
        $this->timers[$timerId]->finally(function () use ($timerId): void {
            unset($this->timers[$timerId]);
        });

        return $timerId;
    }

    private static function toMilliseconds(float|int $seconds): int
    {
        return (int) \round($seconds * 1000);
    }
}
