<?php

declare(strict_types=1);

namespace IfCastle\Application\WorkerPool;

interface WorkerPoolInterface
{
    /**
     * @return WorkerStateInterface[]
     */
    public function getAllWorkerState(): array;

    public function getWorkerState(int $workerId): WorkerStateInterface;

    /**
     * @return WorkerGroupInterface[]
     */
    public function getWorkerGroups(): array;

    public function findGroup(int|string $groupIdOrName): WorkerGroupInterface|null;

    public function isWorkerRunning(int $workerId): bool;

    /**
     * Try to restart the worker by the worker id.
     *
     * The method returns true if the worker was found and running.
     */
    public function restartWorker(int $workerId): bool;
}
