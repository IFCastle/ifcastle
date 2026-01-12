<?php

declare(strict_types=1);

namespace IfCastle\Amphp;

use Amp\DeferredCancellation;
use IfCastle\Async\CancellationInterface;
use IfCastle\Async\DeferredCancellationInterface;

final readonly class DeferredCancellationAdapter implements DeferredCancellationInterface
{
    public function __construct(public DeferredCancellation $deferredCancellation) {}

    #[\Override] public function getCancellation(): CancellationInterface
    {
        return new CancellationExternalAdapter($this->deferredCancellation->getCancellation());
    }

    #[\Override] public function isCancelled(): bool
    {
        return $this->deferredCancellation->isCancelled();
    }

    #[\Override] public function cancel(?\Throwable $previous = null): void
    {
        $this->deferredCancellation->cancel($previous);
    }
}
