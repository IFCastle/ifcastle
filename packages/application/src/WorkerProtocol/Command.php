<?php

declare(strict_types=1);

namespace IfCastle\Application\WorkerProtocol;

use IfCastle\ServiceManager\CommandDescriptorInterface;

final readonly class Command implements CommandDescriptorInterface
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private string $service,
        private string $command,
        private array $parameters = []
    ) {}

    #[\Override]
    public function getServiceNamespace(): string
    {
        return '';
    }

    #[\Override]
    public function getServiceName(): string
    {
        return $this->service;
    }

    #[\Override]
    public function getMethodName(): string
    {
        return $this->command;
    }

    #[\Override]
    public function getCommandName(): string
    {
        return $this->service . '::' . $this->command;
    }

    #[\Override]
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
