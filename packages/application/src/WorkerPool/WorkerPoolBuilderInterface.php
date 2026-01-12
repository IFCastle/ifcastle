<?php

declare(strict_types=1);

namespace IfCastle\Application\WorkerPool;

interface WorkerPoolBuilderInterface
{
    public function describeGroup(WorkerGroupInterface $group): void;
}
