<?php

declare(strict_types=1);

namespace IfCastle\Events;

interface EventDispatcherAwareInterface
{
    public function getEventDispatcher(): EventDispatcherInterface;
}
