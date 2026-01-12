<?php

declare(strict_types=1);

namespace IfCastle\Application\Environment;

use IfCastle\Application\EngineInterface;
use IfCastle\Application\ExecutionRolesEnum;
use IfCastle\Application\RequestEnvironment\RequestEnvironmentInterface;
use IfCastle\Async\CoroutineContextInterface;
use IfCastle\Async\CoroutineSchedulerInterface;
use IfCastle\DI\ContainerInterface;
use IfCastle\DI\DependencyInterface;
use IfCastle\DI\InitializerInterface;
use IfCastle\DI\ResolverInterface;

class SystemEnvironment extends Environment implements SystemEnvironmentInterface
{
    /**
     * @param array<class-string|string, DependencyInterface|InitializerInterface|object|\Throwable|\WeakReference|scalar|null> $container
     */
    public function __construct(ResolverInterface   $resolver,
        array               $container = [],
        ?ContainerInterface $parentContainer = null,
        bool                $isWeakParent = false
    ) {
        parent::__construct($resolver, $container, $parentContainer, $isWeakParent);

        // define self-reference as SystemEnvironmentInterface
        if (false === \array_key_exists(SystemEnvironmentInterface::class, $this->container)) {
            $this->container[SystemEnvironmentInterface::class] = \WeakReference::create($this);
        }
    }

    #[\Override]
    public function getEngine(): EngineInterface
    {
        return $this->resolveDependency(EngineInterface::class);
    }

    #[\Override]
    public function getApplicationDirectory(): string
    {
        return $this->get(self::APPLICATION_DIR);
    }

    #[\Override]
    public function getCoroutineContext(): CoroutineContextInterface|null
    {
        return $this->findDependency(CoroutineContextInterface::class);
    }

    #[\Override]
    public function getCoroutineScheduler(): CoroutineSchedulerInterface|null
    {
        return $this->findDependency(CoroutineSchedulerInterface::class);
    }

    #[\Override]
    public function getRequestEnvironment(): RequestEnvironmentInterface|null
    {
        return $this->findDependency(RequestEnvironmentInterface::class);
    }

    #[\Override]
    public function setRequestEnvironment(RequestEnvironmentInterface $requestEnvironment): void
    {
        $this->set(RequestEnvironmentInterface::class, \WeakReference::create($requestEnvironment));
    }

    #[\Override]
    public function isDeveloperMode(): bool
    {
        return $this->is(self::IS_DEVELOPER_MODE);
    }

    #[\Override]
    public function isTestMode(): bool
    {
        return false;
    }

    #[\Override]
    public function isWebServer(): bool
    {
        return $this->getEngine()->isServer();
    }

    #[\Override]
    public function isJobProcess(): bool
    {
        return $this->getEngine()->isProcess();
    }

    #[\Override]
    public function getExecutionRoles(): array
    {
        return $this->get(self::EXECUTION_ROLES) ?? [];
    }

    #[\Override]
    public function getRuntimeTags(): array
    {
        return $this->get(self::RUNTIME_TAGS) ?? [];
    }

    #[\Override]
    public function isRoleWebServer(): bool
    {
        return \in_array(ExecutionRolesEnum::WEB_SERVER->value, $this->getExecutionRoles(), true);
    }

    #[\Override]
    public function isRoleJobsServer(): bool
    {
        return \in_array(ExecutionRolesEnum::JOB_SERVER->value, $this->getExecutionRoles(), true);
    }
}
