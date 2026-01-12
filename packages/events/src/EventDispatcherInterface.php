<?php

declare(strict_types=1);

namespace IfCastle\Events;

interface EventDispatcherInterface extends \Psr\EventDispatcher\EventDispatcherInterface
{
    public function dispatchEvent(EventInterface $event): void;
}
