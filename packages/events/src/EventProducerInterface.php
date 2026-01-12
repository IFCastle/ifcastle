<?php

declare(strict_types=1);

namespace IfCastle\Events;

interface EventProducerInterface
{
    public function produce(EventInterface $event): void;
}
