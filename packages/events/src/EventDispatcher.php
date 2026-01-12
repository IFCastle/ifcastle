<?php

declare(strict_types=1);

namespace IfCastle\Events;

use IfCastle\Exceptions\UnexpectedValueType;

class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(private EventHandlerInterface|null $eventHandler) {}

    #[\Override]
    public function dispatchEvent(EventInterface $event): void
    {
        $this->eventHandler?->handleEvent($event);
    }

    /**
     * @throws UnexpectedValueType
     */
    #[\Override]
    public function dispatch(object $event): void
    {
        if ($event instanceof EventInterface) {
            $this->dispatchEvent($event);
        }

        throw new UnexpectedValueType('$event', $event, EventInterface::class);
    }

    public function dispose(): void
    {
        $this->eventHandler         = null;
    }
}
