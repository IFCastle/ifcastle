<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

interface ExecutorInterface
{
    /**
     * Executes a command.
     *
     * @param array<string, mixed>              $parameters
     *
     */
    public function executeCommand(
        string|CommandDescriptorInterface $service,
        ?string                            $command      = null,
        array                              $parameters   = [],
        ?ExecutionContextInterface         $context      = null
    ): mixed;
}
