<?php

declare(strict_types=1);

namespace IfCastle\Events;

interface ListenerProviderInterface
{
    public function addEventListener(EventHandlerInterface $eventHandler): static;

    public function removeEventListener(EventHandlerInterface $eventHandler): static;

    public function isListen(EventHandlerInterface $eventHandler): bool;
}
