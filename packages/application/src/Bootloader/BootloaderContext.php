<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader;

use IfCastle\Application\Bootloader\Builder\PublicEnvironmentBuilderInterface;
use IfCastle\Application\Environment\SystemEnvironmentInterface;
use IfCastle\Application\RequestEnvironment\RequestPlanInterface;
use IfCastle\DI\BuilderInterface;
use IfCastle\DI\ConfigInterface;
use IfCastle\DI\Container;
use IfCastle\DI\ContainerMutableTrait;

class BootloaderContext extends Container implements BootloaderContextInterface
{
    use ContainerMutableTrait;

    protected bool $isWarmUpEnabled = false;

    #[\Override]
    public function getApplicationDirectory(): string
    {
        return $this->container[self::APPLICATION_DIRECTORY] ?? '';
    }

    #[\Override]
    public function getApplicationType(): string
    {
        return $this->container[self::APPLICATION_TYPE] ?? '';
    }

    #[\Override]
    public function getExecutionRoles(): array
    {
        return $this->container[SystemEnvironmentInterface::EXECUTION_ROLES] ?? [];
    }

    #[\Override]
    public function getRuntimeTags(): array
    {
        return $this->container[SystemEnvironmentInterface::RUNTIME_TAGS] ?? [];
    }

    #[\Override]
    public function isWarmUpEnabled(): bool
    {
        return $this->isWarmUpEnabled;
    }

    #[\Override]
    public function enabledWarmUp(): static
    {
        $this->isWarmUpEnabled      = true;
        return $this;
    }

    #[\Override]
    public function getApplicationConfig(): ConfigInterface
    {
        return $this->resolveDependency(ConfigInterface::class);
    }

    #[\Override]
    public function getSystemEnvironmentBootBuilder(): BuilderInterface
    {
        return $this->resolveDependency(BuilderInterface::class);
    }

    #[\Override]
    public function getPublicEnvironmentBootBuilder(): PublicEnvironmentBuilderInterface
    {
        return $this->resolveDependency(PublicEnvironmentBuilderInterface::class);
    }

    #[\Override]
    public function getRequestEnvironmentPlan(): RequestPlanInterface
    {
        return $this->resolveDependency(RequestPlanInterface::class);
    }

    #[\Override]
    public function getSystemEnvironment(): SystemEnvironmentInterface|null
    {
        return $this->findDependency(SystemEnvironmentInterface::class);
    }
}
