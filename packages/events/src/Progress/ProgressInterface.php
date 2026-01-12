<?php

declare(strict_types=1);

namespace IfCastle\Events\Progress;

use IfCastle\Events\EventInterface;

/**
 * ## ProgressInterface.
 *
 */
interface ProgressInterface extends EventInterface
{
    /**
     * @var string
     */
    final public const EVENT_PROGRESS   = 'eventProgress';

    public function isProgressCompleted(): bool;

    public function isProgressProcessing(): bool;
}
