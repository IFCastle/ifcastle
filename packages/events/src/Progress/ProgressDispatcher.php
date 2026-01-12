<?php

declare(strict_types=1);

namespace IfCastle\Events\Progress;

use IfCastle\Events\EventDispatcher;
use IfCastle\TypeDefinitions\ResultInterface;

class ProgressDispatcher extends EventDispatcher implements ProgressDispatcherInterface
{
    #[\Override]
    public function dispatchProgress(ProgressInterface $event): void
    {
        $this->dispatchEvent($event);
    }

    #[\Override]
    public function progressPercentage(int $percentage, ?string $description = null, int $eventTimestamp = 0): void
    {
        $this->dispatchEvent(new ProgressPercentageEvent($percentage, $description, $eventTimestamp));
    }

    #[\Override]
    public function progressItem(int $current, int $total, string $itemName = '', int $eventTimestamp = 0): void
    {
        $this->dispatchEvent(new ProgressByItemEvent($current, $total, $itemName, null, $eventTimestamp));
    }

    #[\Override]
    public function progressItemEnd(
        int     $current,
        int     $total,
        ResultInterface $result,
        string  $itemName = '',
        int     $eventTimestamp = 0
    ): void {
        $this->dispatchEvent(new ProgressByItemEvent($current, $total, $itemName, $result, $eventTimestamp));
    }
}
