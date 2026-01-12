<?php

declare(strict_types=1);

namespace IfCastle\Application\WorkerPool;

final readonly class WorkerGroup implements WorkerGroupInterface
{
    public function __construct(
        public string                   $entryPointClass,
        public WorkerTypeEnum           $workerType,
        public int                      $minWorkers      = 0,
        public int                      $maxWorkers      = 0,
        public string                   $groupName       = ''
    ) {}

    #[\Override]
    public function getEntryPointClass(): string
    {
        return $this->entryPointClass;
    }

    #[\Override]
    public function getWorkerType(): WorkerTypeEnum
    {
        return $this->workerType;
    }

    #[\Override]
    public function getWorkerGroupId(): int
    {
        return 0;
    }

    #[\Override] public function getMinWorkers(): int
    {
        return $this->minWorkers;
    }

    #[\Override] public function getMaxWorkers(): int
    {
        return $this->maxWorkers;
    }

    #[\Override] public function getGroupName(): string
    {
        return $this->groupName;
    }
}
