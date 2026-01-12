<?php

declare(strict_types=1);

namespace IfCastle\Events;

interface EventHandlerInterface
{
    public function handleEvent(EventInterface $event): void;
}
