<?php

declare(strict_types=1);

namespace IfCastle\Events;

/**
 * ## EventInterface.
 *
 * Defining an Event for an Event-Driven Application with Queues.
 *
 * @see https://en.wikipedia.org/wiki/Event-driven_architecture
 */
interface EventInterface
{
    final public const string EVENT_NAME = 'name';

    final public const string EVENT_TIMESTAMP = 'timestamp';

    public function getEventName(): string;

    public function getEventTimestamp(): int;
}
