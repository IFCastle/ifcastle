<?php

declare(strict_types=1);

namespace IfCastle\Application\WorkerPool;

interface WorkerGroupInterface
{
    public function getEntryPointClass(): string;

    public function getWorkerType(): WorkerTypeEnum;

    public function getWorkerGroupId(): int;

    public function getMinWorkers(): int;

    public function getMaxWorkers(): int;

    public function getGroupName(): string;
}
