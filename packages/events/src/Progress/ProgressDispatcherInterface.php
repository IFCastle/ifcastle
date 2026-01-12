<?php

declare(strict_types=1);

namespace IfCastle\Events\Progress;

use IfCastle\Events\EventDispatcherInterface;
use IfCastle\TypeDefinitions\ResultInterface;

interface ProgressDispatcherInterface extends EventDispatcherInterface
{
    /**
     * Propagates the progress event.
     */
    public function dispatchProgress(ProgressInterface $event): void;

    /**
     * Propagates the percentage progress event.
     *
     */
    public function progressPercentage(int $percentage, ?string $description = null, int $eventTimestamp = 0): void;

    /**
     * Propagates the item progress event.
     */
    public function progressItem(int $current, int $total, string $itemName = '', int $eventTimestamp = 0): void;

    public function progressItemEnd(int $current, int $total, ResultInterface $result, string $itemName = '', int $eventTimestamp = 0): void;
}
