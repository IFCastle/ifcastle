<?php

declare(strict_types=1);

namespace IfCastle\Application\WorkerPool;

interface ScaleInterface
{
    /**
     * Scale workers in the group.
     * Returns the number of workers that were actually scaled (started or shutdown).
     *
     * The parameter $count can be negative
     * in this case, the method will try to stop the workers.
     *
     *
     */
    public function scaleWorkers(int $groupId, int $delta): int;
}
