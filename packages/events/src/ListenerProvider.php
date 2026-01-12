<?php

declare(strict_types=1);

namespace IfCastle\Events;

class ListenerProvider implements ListenerProviderInterface, EventHandlerInterface, EventDispatcherAwareInterface
{
    /**
     * @var EventHandlerInterface[]
     */
    private array $listeners = [];

    #[\Override]
    public function handleEvent(EventInterface $event): void
    {
        foreach ($this->listeners as $listener) {
            $listener->handleEvent($event);
        }
    }

    #[\Override]
    public function addEventListener(EventHandlerInterface $eventHandler): static
    {
        $id                         = \spl_object_id($eventHandler);

        if (\array_key_exists($id, $this->listeners)) {
            return $this;
        }

        $this->listeners[$id]       = $eventHandler;
        return $this;
    }

    #[\Override]
    public function removeEventListener(EventHandlerInterface $eventHandler): static
    {
        $id                         = \spl_object_id($eventHandler);

        if (\array_key_exists($id, $this->listeners)) {
            unset($this->listeners[$id]);
        }

        return $this;
    }

    #[\Override]
    public function isListen(EventHandlerInterface $eventHandler): bool
    {
        return \array_key_exists(\spl_object_id($eventHandler), $this->listeners);
    }

    #[\Override]
    public function getEventDispatcher(): EventDispatcherInterface
    {
        return new EventDispatcher($this);
    }

    public function dispose(): void
    {
        $this->listeners            = [];
    }
}
