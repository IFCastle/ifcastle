<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader;

use IfCastle\DesignPatterns\ExecutionPlan\BeforeAfterExecutorInterface;
use IfCastle\DesignPatterns\ExecutionPlan\ExecutionPlanInterface;

interface BootloaderExecutorInterface extends BeforeAfterExecutorInterface, ExecutionPlanInterface
{
    public function getBootloaderContext(): BootloaderContextInterface;

    public function defineStartApplicationHandler(callable $handler): static;

    public function addWarmUpOperation(callable $handler): static;

    public function runAfterEngine(callable $handler): static;

    /**
     * @return callable[]
     */
    public function getEngineAfterHandlers(): array;
}
