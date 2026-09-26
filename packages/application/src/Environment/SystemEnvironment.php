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
        $coroutineContext           = $this->getCoroutineContext();

        if ($coroutineContext === null) {
            return $this->findDependency(RequestEnvironmentInterface::class);
        }

        // Concurrent requests share this environment, so the current one is kept per request.
        $reference                  = $coroutineContext->getForRequest(RequestEnvironmentInterface::class);

        return $reference instanceof \WeakReference ? $reference->get() : null;
    }

    #[\Override]
    public function setRequestEnvironment(RequestEnvironmentInterface $requestEnvironment): void
    {
        $reference                  = \WeakReference::create($requestEnvironment);
        $coroutineContext           = $this->getCoroutineContext();

        if ($coroutineContext === null) {
            // The root holds it, so every environment in the chain finds it: public reads from system.
            $this->rootSystemEnvironment()->set(RequestEnvironmentInterface::class, $reference);
        } else {
            $coroutineContext->setForRequest(RequestEnvironmentInterface::class, $reference);
        }
    }

    private function rootSystemEnvironment(): self
    {
        $root                       = $this;

        while (($parent = $root->getParentContainer()) instanceof self) {
            $root                   = $parent;
        }

        return $root;
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
