<?php

declare(strict_types=1);

namespace IfCastle\Amphp;

use IfCastle\Amphp\Internal\Scheduler;
use IfCastle\Async\CoroutineInterface;

readonly class CoroutineAdapter implements CoroutineInterface
{
    public function __construct(private string $id) {}

    #[\Override]
    public function getCoroutineId(): int|string
    {
        return $this->id;
    }

    #[\Override]
    public function isRunning(): bool
    {
        return Scheduler::default()->findCoroutine($this->id)?->getStartAt() > 0;
    }

    #[\Override]
    public function isCancelled(): bool
    {
        return Scheduler::default()->findCoroutine($this->id)?->isCancelled();
    }

    #[\Override]
    public function isFinished(): bool
    {
        return Scheduler::default()->findCoroutine($this->id)?->isFinished();
    }

    #[\Override]
    public function stop(?\Throwable $throwable = null): bool
    {
        return Scheduler::default()->stop($this->id);
    }
}
