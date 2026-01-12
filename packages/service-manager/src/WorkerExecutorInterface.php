<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

/**
 * ## WorkerExecutorInterface.
 *
 * This interface provides the ability to execute commands in worker processes.
 * It differs from the Command-bus interface of ExecutorInterface by explicitly specifying the execution method.
 */
interface WorkerExecutorInterface
{
    /**
     * @param array<string, mixed>               $parameters
     *
     */
    public function executeCommandInWorker(
        string|CommandDescriptorInterface $service,
        ?string                            $command      = null,
        array                              $parameters   = [],
        ?ExecutionContextInterface         $context      = null
    ): mixed;

    /**
     * @param array<string, mixed>               $parameters
     *
     */
    public function executeCommandInWorkerAsync(
        string|CommandDescriptorInterface $service,
        ?string                            $command      = null,
        array                              $parameters   = [],
        ?ExecutionContextInterface         $context      = null
    ): int|string;
}
