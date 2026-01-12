<?php

declare(strict_types=1);

namespace IfCastle\Amphp\Internal;

use Amp\Cancellation;
use Amp\CancelledException;
use IfCastle\Async\CancellationInterface;

readonly class CancellationAdapter implements Cancellation
{
    public function __construct(private CancellationInterface $cancellation) {}

    #[\Override] public function subscribe(\Closure $callback): string
    {
        return $this->cancellation->subscribe($callback);
    }

    #[\Override] public function unsubscribe(string $id): void
    {
        $this->cancellation->unsubscribe($id);
    }

    #[\Override] public function isRequested(): bool
    {
        return $this->cancellation->isRequested();
    }

    #[\Override] public function throwIfRequested(): void
    {
        if ($this->cancellation->isRequested()) {
            throw new CancelledException();
        }
    }
}
