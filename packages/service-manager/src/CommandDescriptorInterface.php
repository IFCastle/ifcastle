<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

interface CommandDescriptorInterface
{
    public function getServiceNamespace(): string;

    public function getServiceName(): string;

    public function getMethodName(): string;

    /**
     * Returns getServiceName + getMethodName.
     */
    public function getCommandName(): string;

    /**
     * Returns the list of parameters for the command.
     * @return array<string, mixed>
     */
    public function getParameters(): array;
}
