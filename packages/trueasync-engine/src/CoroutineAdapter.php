<?php

declare(strict_types=1);

namespace IfCastle\TrueAsync;

use Async\AsyncCancellation;
use Async\Coroutine;
use IfCastle\Async\CoroutineInterface;
use IfCastle\TrueAsync\Exceptions\CancelledException;

/**
 * CoroutineInterface over a TrueAsync coroutine.
 */
final readonly class CoroutineAdapter implements CoroutineInterface
{
    public function __construct(private Coroutine $coroutine) {}

    #[\Override]
    public function getCoroutineId(): int
    {
        return $this->coroutine->getId();
    }

    #[\Override]
    public function isRunning(): bool
    {
        return $this->coroutine->isStarted() && false === $this->coroutine->isCompleted();
    }

    #[\Override]
    public function isCancelled(): bool
    {
        return $this->coroutine->isCancelled();
    }

    #[\Override]
    public function isFinished(): bool
    {
        return $this->coroutine->isCompleted();
    }

    /**
     * Requests cancellation. A throwable that is not an AsyncCancellation becomes the previous
     * exception of a CancelledException, since TrueAsync cancels only with AsyncCancellation.
     *
     * @return bool false when the coroutine has already completed.
     */
    #[\Override]
    public function stop(?\Throwable $throwable = null): bool
    {
        if ($this->coroutine->isCompleted()) {
            return false;
        }

        $this->coroutine->cancel(self::toCancellation($throwable));

        return true;
    }

    public static function toCancellation(?\Throwable $throwable): AsyncCancellation
    {
        if ($throwable instanceof AsyncCancellation) {
            return $throwable;
        }

        return new CancelledException('Coroutine stopped', 0, $throwable);
    }
}
