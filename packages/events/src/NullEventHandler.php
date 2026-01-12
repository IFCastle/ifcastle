<?php

declare(strict_types=1);

namespace IfCastle\Events;

/**
 * ## NullEventHandler.
 *
 * An event handler that does nothing
 *
 */
final class NullEventHandler implements EventHandlerInterface
{
    #[\Override]
    public function handleEvent(EventInterface $event): void {}
}
