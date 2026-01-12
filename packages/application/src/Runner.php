<?php

declare(strict_types=1);

namespace IfCastle\Application;

use IfCastle\Application\Bootloader\BootloaderExecutorInterface;
use IfCastle\Application\Bootloader\Builder\BootloaderBuilderByIniFiles;
use IfCastle\Application\Bootloader\Builder\BootloaderBuilderInterface;
use IfCastle\Application\Environment\SystemEnvironmentInterface;
use IfCastle\DI\DisposableInterface;

/**
 * Application startup strategy.
 * This module defines the algorithm and sequence for starting the application.
 *
 * @startuml
 * actor User
 * participant Runner
 * participant BootloaderBuilder
 * participant BootloaderExecutor
 * participant Application
 * participant SystemEnvironment
 *
 * User -> Runner: run()
 * Runner -> Runner: buildBootloader()
 * Runner -> BootloaderBuilder: getBootloaderBuilder()
 * BootloaderBuilder -> BootloaderBuilder: build()
 * BootloaderBuilder -> BootloaderExecutor: getBootloader()
 * Runner <- BootloaderBuilder: BootloaderExecutor
 *
 * Runner -> BootloaderExecutor: execute()
 * BootloaderExecutor -> BootloaderExecutor: defineStartApplicationHandler()
 * BootloaderExecutor -> Application: create Application(appDir, systemEnvironment)
 * Application -> Application: start()
 *
 * Runner -> BootloaderExecutor: executePlan()
 * BootloaderExecutor -> Application: getEngineAfterHandlers()
 * Application -> Application: defineAfterEngineHandlers()
 *
 * Runner -> Application: engineStart()
 * Application -> Application: start engine
 *
 * Runner -> Runner: dispose()
 * Runner -> Application: end()
 * Runner -> BootloaderExecutor: dispose()
 * @enduml
 *
 */
class Runner implements DisposableInterface
{
    protected ApplicationInterface|null $application = null;

    protected BootloaderBuilderInterface|null $bootloaderBuilder = null;

    /**
     * If true, the runner will throw exceptions from the engine.
     */
    protected bool $throwEngineException = false;

    public function __construct(
        protected readonly string $appDir,
        protected readonly string $appType,
        protected readonly string $applicationClass,
        /**
         * `runtimeTags`
         *  are used to determine the visibility of services, components, and dependencies.
         *
         * An array of string values that is set at application startup and never changes afterward.
         * The array characterizes the purpose of the application, its configuration, and its features.
         *
         * Runtime tags are similar to executionRoles, which have the same meaning but are defined in the configuration.
         *
         * @var array<string>
         */
        protected array $runtimeTags = []
    ) {}

    /**
     * @throws \Throwable
     */
    final public function runAndDispose(): void
    {
        try {
            $this->startEngine($this->buildBootloader());
        } finally {
            $this->dispose();
        }
    }

    /**
     * @throws \Throwable
     */
    final public function run(): ApplicationInterface
    {
        $this->startEngine($this->buildBootloader());
        return $this->application;
    }

    final public function runAndExit(): never
    {
        try {
            $this->startEngine($this->buildBootloader());
        } catch (\Throwable $throwable) {
            echo $throwable->getMessage() . ' in ' . $throwable->getFile() . ':' . $throwable->getLine();
            exit(1);
        }

        exit(0);
    }

    public function __destruct()
    {
        $this->dispose();
    }

    public function defineBootloaderBuilder(BootloaderBuilderInterface $bootloaderBuilder): static
    {
        $this->bootloaderBuilder    = $bootloaderBuilder;
        return $this;
    }

    public function withRuntimeTags(string ...$runtimeTags): static
    {
        $this->runtimeTags           = \array_unique(\array_merge($this->runtimeTags, $runtimeTags));
        return $this;
    }

    protected function getBootloaderBuilder(): BootloaderBuilderInterface
    {
        if ($this->bootloaderBuilder !== null) {
            return $this->bootloaderBuilder;
        }

        $this->bootloaderBuilder = new BootloaderBuilderByIniFiles(
            $this->appDir, $this->appDir . '/bootloader', $this->appType, $this->runtimeTags
        );

        return $this->bootloaderBuilder;
    }

    protected function predefineEngine(BootloaderExecutorInterface $bootloaderExecutor): void {}

    protected function postConfigureBootloader(BootloaderExecutorInterface $bootloaderExecutor): void {}

    protected function buildBootloader(): BootloaderExecutorInterface
    {
        $bootloaderBuilder          = $this->getBootloaderBuilder();

        $bootloaderBuilder->build();

        $bootloader                 = $bootloaderBuilder->getBootloader();
        $this->bootloaderBuilder    = null;

        $this->predefineEngine($bootloader);
        $this->postConfigureBootloader($bootloader);

        return $bootloader;
    }

    protected function startEngine(BootloaderExecutorInterface $bootloader): void
    {
        try {
            $bootloader->defineStartApplicationHandler(function (SystemEnvironmentInterface $systemEnvironment) {
                $this->application  = new ($this->applicationClass)($this->appDir, $systemEnvironment);
                $this->application->start();
            });

            try {
                $bootloader->executePlan();
                $this->application->defineAfterEngineHandlers($bootloader->getEngineAfterHandlers());
            } finally {

                if ($bootloader instanceof DisposableInterface) {
                    $bootloader->dispose();
                }

                unset($bootloader);
            }

            // Start the engine
            $this->application->engineStart();

        } catch (\Throwable $throwable) {
            $this->application?->criticalLog($throwable);

            if ($this->application === null || $this->throwEngineException) {
                throw $throwable;
            }
        }
    }

    #[\Override]
    public function dispose(): void
    {
        $this->bootloaderBuilder    = null;
        $this->application?->end();
        $this->application          = null;
    }
}
