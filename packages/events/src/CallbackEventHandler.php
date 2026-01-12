<?php

declare(strict_types=1);

namespace IfCastle\Events;

final class CallbackEventHandler implements EventHandlerInterface
{
    protected mixed $handler        = null;

    public function __construct(callable $handler)
    {
        $this->handler              = $handler;
    }

    #[\Override]
    public function handleEvent(EventInterface $event): void
    {
        \call_user_func($this->handler, $event);
    }

    public function dispose(): void
    {
        $this->handler              = null;
    }
}
