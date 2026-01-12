<?php

declare(strict_types=1);

namespace IfCastle\Application\WorkerPool;

interface WorkerStateInterface
{
    public function getWorkerId(): int;

    public function getGroupId(): int;

    public function isShouldBeStarted(): bool;

    public function getPid(): int;
}
