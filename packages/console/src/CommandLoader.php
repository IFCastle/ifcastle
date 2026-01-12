<?php

declare(strict_types=1);

namespace IfCastle\Console;

use IfCastle\DI\ContainerInterface;
use IfCastle\ServiceManager\DescriptorRepositoryInterface;
use IfCastle\ServiceManager\DescriptorWalker;
use IfCastle\ServiceManager\ServiceDescriptorInterface;
use IfCastle\TypeDefinitions\FunctionDescriptorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class CommandLoader implements CommandLoaderInterface
{
    /**
     * @var ServiceCommand[]|null
     */
    protected ?array $commands      = null;

    public function __construct(
        protected ContainerInterface $container,
        protected DescriptorRepositoryInterface $descriptorRepository
    ) {}


    #[\Override]
    public function get(string $name): Command
    {
        $commandClass               = $this->foundCommand($name);

        if ($commandClass === null) {
            throw new CommandNotFoundException($name);
        }

        return $this->instantiateCommand($commandClass);
    }

    #[\Override]
    public function has(string $name): bool
    {
        return $this->foundCommand($name) !== null;
    }

    #[\Override]
    public function getNames(): array
    {
        if ($this->commands === null) {
            $this->buildCommands();
        }

        return \array_keys($this->commands);
    }

    protected function foundCommand(string $name): ?ServiceCommand
    {
        if ($this->commands === null) {
            $this->buildCommands();
        }

        return $this->commands[$name] ?? null;
    }

    protected function instantiateCommand(ServiceCommand $command): Command
    {
        $command->resolveDependencies($this->container);

        return $command;
    }

    protected function buildCommands(): void
    {
        $this->commands             = [];

        foreach (DescriptorWalker::walkWithService($this->descriptorRepository) as $serviceName => [$serviceDescriptor, $methodDescriptor]) {

            //@phpstan-ignore-next-line
            if (false === $methodDescriptor instanceof FunctionDescriptorInterface || false === $serviceDescriptor instanceof ServiceDescriptorInterface) {
                continue;
            }

            $console                = $methodDescriptor->findAttribute(AsConsole::class);

            if ($console === null) {
                continue;
            }

            $commandName            = CommandBuildHelper::getCommandName($console, $methodDescriptor, $serviceDescriptor, $serviceName);

            $this->commands[$commandName] = new ServiceCommand(
                $commandName,
                $serviceName,
                $methodDescriptor->getName(),
                CommandBuildHelper::buildArgumentsAndOptions($methodDescriptor),
                $console->aliases,
                $console->help,
                $console->description,
                $console->hidden
            );
        }
    }
}
