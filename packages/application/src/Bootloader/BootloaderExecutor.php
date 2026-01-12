<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader;

use IfCastle\Application\Bootloader\Builder\PublicEnvironmentBuilder;
use IfCastle\Application\Bootloader\Builder\PublicEnvironmentBuilderInterface;
use IfCastle\Application\Bootloader\Builder\SystemEnvironmentBuilder;
use IfCastle\Application\Environment\PublicEnvironmentInterface;
use IfCastle\Application\Environment\SystemEnvironmentInterface;
use IfCastle\Application\RequestEnvironment\RequestPlan;
use IfCastle\Application\RequestEnvironment\RequestPlanInterface;
use IfCastle\DesignPatterns\ExecutionPlan\BeforeAfterExecutor;
use IfCastle\DI\BuilderInterface;
use IfCastle\DI\ConfigInterface;
use IfCastle\DI\DisposableInterface;
use IfCastle\DI\Resolver;
use IfCastle\DI\ResolverInterface;
use IfCastle\Exceptions\UnexpectedValueType;

class BootloaderExecutor extends BeforeAfterExecutor implements BootloaderExecutorInterface, DisposableInterface
{
    private const string WARM_UP    = 'warm-up';

    protected BootloaderContextInterface $bootloaderContext;

    protected mixed $startApplicationHandler = null;

    /**
     * @var callable[]
     */
    protected array $afterEngineHandlers = [];

    /**
     * @param string[] $executionRoles
     * @param string[] $runtimeTags
     */
    public function __construct(
        protected ConfigInterface $config,
        protected readonly string $applicationType,
        array $executionRoles = [],
        array $runtimeTags = []
    ) {
        $this->initContext();
        $this->defineExecutionRolesAndRuntimeTags($executionRoles, $runtimeTags);

        parent::__construct(new HandlerExecutor($this, $this->bootloaderContext));

        $this->stages[self::WARM_UP] = [];

        // Main stage
        $this->addHandler($this->startApplication(...));
    }

    #[\Override]
    public function getBootloaderContext(): BootloaderContextInterface
    {
        return $this->bootloaderContext;
    }


    #[\Override]
    public function dispose(): void
    {
        $this->stages              = [];
        $this->afterEngineHandlers = [];
    }

    public function getSystemEnvironmentBootBuilder(): BuilderInterface
    {
        return $this->bootloaderContext->getSystemEnvironmentBootBuilder();
    }

    public function getRequestEnvironmentPlan(): RequestPlanInterface
    {
        return $this->bootloaderContext->getRequestEnvironmentPlan();
    }

    #[\Override]
    public function defineStartApplicationHandler(callable $handler): static
    {
        $this->startApplicationHandler   = $handler;
        return $this;
    }

    #[\Override]
    public function addWarmUpOperation(callable $handler): static
    {
        return $this->addStageHandler(self::WARM_UP, $handler);
    }

    #[\Override]
    public function runAfterEngine(callable $handler): static
    {
        $this->afterEngineHandlers[] = $handler;
        return $this;
    }

    #[\Override]
    public function getEngineAfterHandlers(): array
    {
        return $this->afterEngineHandlers;
    }

    protected function initContext(): void
    {
        $this->bootloaderContext = new BootloaderContext(new Resolver(), [
            SystemEnvironmentInterface::EXECUTION_ROLES => [],
            SystemEnvironmentInterface::RUNTIME_TAGS    => [],
            ConfigInterface::class                      => $this->config,
            BootloaderContextInterface::APPLICATION_TYPE => $this->applicationType,
            BootloaderExecutorInterface::class          => \WeakReference::create($this),
            BuilderInterface::class                     => new SystemEnvironmentBuilder(),
            PublicEnvironmentBuilderInterface::class    => new PublicEnvironmentBuilder(),
            RequestPlanInterface::class                 => new RequestPlan(),
        ]);
    }

    /**
     * @param string[] $executionRoles
     * @param string[] $runtimeTags
     *
     */
    protected function defineExecutionRolesAndRuntimeTags(array $executionRoles, array $runtimeTags): void
    {
        foreach ($this->config->findSection(SystemEnvironmentInterface::EXECUTION_ROLES) as $role => $value) {
            if (!empty($value)) {
                $executionRoles[]   = $role;
            }
        }

        $runtimeTags                = \array_merge($runtimeTags, $executionRoles, [$this->applicationType]);
        $executionRoles             = \array_unique($executionRoles);
        $runtimeTags                = \array_unique($runtimeTags);

        //
        $this->bootloaderContext->set(SystemEnvironmentInterface::EXECUTION_ROLES, $executionRoles);
        $this->getSystemEnvironmentBootBuilder()->set(SystemEnvironmentInterface::EXECUTION_ROLES, $executionRoles);

        $this->bootloaderContext->set(SystemEnvironmentInterface::RUNTIME_TAGS, $runtimeTags);
        $this->getSystemEnvironmentBootBuilder()->set(SystemEnvironmentInterface::RUNTIME_TAGS, $runtimeTags);
    }

    /**
     * @throws UnexpectedValueType
     */
    protected function startApplication(): void
    {
        if ($this->startApplicationHandler === null) {
            return;
        }

        $bootBuilder                = $this->getSystemEnvironmentBootBuilder();
        $bootBuilder->set(SystemEnvironmentInterface::APPLICATION_DIR, $this->bootloaderContext->getApplicationDirectory());

        // Build system environment
        $bootBuilder->bindObject(RequestPlanInterface::class, $this->getRequestEnvironmentPlan()->asImmutable());

        // Assign app-config
        if (false === $bootBuilder->isBound(ConfigInterface::class)) {
            $bootBuilder->bindObject(ConfigInterface::class, $this->bootloaderContext->getApplicationConfig());
        }

        $systemEnvironment          = $bootBuilder->buildContainer($this->getDependencyResolver());

        if (false === $systemEnvironment instanceof SystemEnvironmentInterface) {
            throw new UnexpectedValueType('SystemEnvironmentInterface', $systemEnvironment, SystemEnvironmentInterface::class);
        }

        $builder                    = $this->bootloaderContext->getPublicEnvironmentBootBuilder();

        $publicEnvironment          = $builder->buildContainer($this->getDependencyResolver(), $systemEnvironment, true);
        $systemEnvironment->set(PublicEnvironmentInterface::class, $publicEnvironment);

        // Assign system environment for bootloader context
        $this->bootloaderContext->set(SystemEnvironmentInterface::class, $systemEnvironment);

        // Start application
        $startApplicationHandler    = $this->startApplicationHandler;
        $this->startApplicationHandler = null;

        $startApplicationHandler($systemEnvironment);
    }

    protected function getDependencyResolver(): ResolverInterface
    {
        return new Resolver();
    }
}
