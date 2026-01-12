<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\Builder;

use IfCastle\Application\Bootloader\BootloaderContextInterface;
use IfCastle\Application\Bootloader\BootloaderExecutor;
use IfCastle\Application\Bootloader\BootloaderExecutorInterface;
use IfCastle\Application\Bootloader\BootloaderInterface;
use IfCastle\Application\Bootloader\ServiceManager\ServiceManagerBootloader;
use IfCastle\Application\Console\ConsoleLogger;
use IfCastle\Application\Console\ConsoleLoggerInterface;
use IfCastle\Application\Console\ConsoleOutputInterface;
use IfCastle\Application\Console\NullOutput;
use IfCastle\Application\Environment\SystemEnvironmentInterface;
use IfCastle\Application\WorkerProtocol\WorkerProtocolArrayTyped;
use IfCastle\Application\WorkerProtocol\WorkerProtocolInterface;
use IfCastle\DI\ConfigInterface;
use IfCastle\ServiceManager\ExecutorInterface;
use IfCastle\ServiceManager\ServiceLocatorInterface;

abstract class BootloaderBuilderAbstract implements BootloaderBuilderInterface
{
    protected string $appDirectory;

    protected BootloaderExecutorInterface|null $bootloader = null;

    protected string $applicationType;

    /**
     * @var array<string>
     */
    protected array  $runtimeTags;

    /**
     * @var array<string>
     */
    protected array $executionRoles = [];

    #[\Override]
    public function getApplicationDirectory(): string
    {
        return $this->appDirectory;
    }

    #[\Override]
    public function getApplicationType(): string
    {
        return $this->applicationType;
    }

    #[\Override]
    public function getExecutionRoles(): array
    {
        return $this->executionRoles;
    }

    #[\Override]
    public function getRuntimeTags(): array
    {
        return $this->runtimeTags;
    }

    #[\Override]
    public function build(): void
    {
        if ($this->bootloader === null) {
            $configurator           = $this->initConfigurator();
            $this->bootloader       = new BootloaderExecutor(
                $configurator, $this->applicationType, $this->executionRoles, $this->runtimeTags
            );

            // Bind the application directory to the bootloader context
            $this->bootloader->getBootloaderContext()->set(BootloaderContextInterface::APPLICATION_DIRECTORY, $this->appDirectory);

            if ($configurator instanceof BootloaderInterface) {
                $configurator->buildBootloader($this->bootloader);
            }

            $this->defineExecutionRoles($configurator);
        }

        foreach ($this->fetchBootloaders() as $bootloaderClass) {
            if (false === \class_exists($bootloaderClass)) {
                throw new \RuntimeException('Bootloader class not found: ' . $bootloaderClass);
            }

            $this->handleBootloaderClass($bootloaderClass);
        }

        $this->defineServiceManagerBootloader();
        $this->defineWorkerProtocol();
        $this->defineConsoleOutput();
    }

    #[\Override]
    public function getBootloader(): BootloaderExecutorInterface
    {
        if ($this->bootloader === null) {
            throw new \RuntimeException('Bootloader not built');
        }

        return $this->bootloader;
    }

    /**
     * Fetch the bootloaders to be executed.
     * @return iterable<string>
     */
    abstract protected function fetchBootloaders(): iterable;

    abstract protected function initConfigurator(): ConfigInterface;

    protected function handleBootloaderClass(string $bootloaderClass): void
    {
        $object                     = new $bootloaderClass();

        if (false === $object instanceof BootloaderInterface) {
            throw new \RuntimeException('Bootloader class must implement BootloaderInterface: ' . $bootloaderClass);
        }

        $object->buildBootloader($this->bootloader);
    }

    protected function defineExecutionRoles(ConfigInterface $configurator): void
    {
        foreach ($configurator->findSection(SystemEnvironmentInterface::EXECUTION_ROLES) as $role => $value) {
            if (!empty($value)) {
                $executionRoles[]   = $role;
            }
        }

        $executionRoles[]           = $this->applicationType;
        $this->executionRoles       = \array_unique($executionRoles);
    }

    /**
     * Define the Service Manager Bootloader if it is not already defined.
     */
    protected function defineServiceManagerBootloader(): void
    {
        $builder                    = $this->bootloader->getBootloaderContext()->getSystemEnvironmentBootBuilder();

        if ($builder->isBound(ServiceLocatorInterface::class, ExecutorInterface::class)) {
            return;
        }

        $this->handleBootloaderClass(ServiceManagerBootloader::class);
    }

    protected function defineWorkerProtocol(): void
    {
        $builder                    = $this->bootloader->getBootloaderContext()->getSystemEnvironmentBootBuilder();

        if ($builder->isBound(WorkerProtocolInterface::class)) {
            return;
        }

        $builder->bindConstructible(WorkerProtocolInterface::class, WorkerProtocolArrayTyped::class);
    }

    protected function defineConsoleOutput(): void
    {
        $builder                    = $this->bootloader->getBootloaderContext()->getSystemEnvironmentBootBuilder();

        if (false === $builder->isBound(ConsoleOutputInterface::class)) {
            $builder->bindObject(ConsoleOutputInterface::class, new NullOutput());
        }

        if (false === $builder->isBound(ConsoleLoggerInterface::class)) {
            $builder->bindConstructible(ConsoleLoggerInterface::class, ConsoleLogger::class);
        }
    }
}
