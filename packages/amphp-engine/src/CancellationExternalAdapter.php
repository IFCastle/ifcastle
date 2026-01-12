<?php

declare(strict_types=1);

namespace IfCastle\Amphp;

use Amp\Cancellation;
use Amp\CancelledException;
use IfCastle\Async\CancellationInterface;

readonly class CancellationExternalAdapter implements CancellationInterface
{
    public function __construct(public Cancellation $cancellation) {}

    #[\Override]
    public function subscribe(\Closure $callback): string
    {
        return $this->cancellation->subscribe($callback);
    }

    #[\Override]
    public function unsubscribe(string $id): void
    {
        $this->cancellation->unsubscribe($id);
    }

    #[\Override]
    public function isRequested(): bool
    {
        return $this->cancellation->isRequested();
    }

    /**
     * @throws CancelledException
     */
    #[\Override]
    public function throwIfRequested(): void
    {
        if ($this->cancellation->isRequested()) {
            throw new CancelledException();
        }
    }
}
