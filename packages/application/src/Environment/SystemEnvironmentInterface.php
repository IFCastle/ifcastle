<?php

declare(strict_types=1);

namespace IfCastle\Application\Environment;

use IfCastle\Application\EngineInterface;
use IfCastle\Application\RequestEnvironment\RequestEnvironmentInterface;
use IfCastle\Async\CoroutineContextInterface;
use IfCastle\Async\CoroutineSchedulerInterface;
use IfCastle\DI\DisposableInterface;

interface SystemEnvironmentInterface extends EnvironmentInterface, DisposableInterface
{
    public const string APPLICATION_DIR     = 'applicationDir';

    public const string EXECUTION_ROLES     = 'executionRoles';

    public const string RUNTIME_TAGS        = 'runtimeTags';

    public const string IS_DEVELOPER_MODE   = 'devMode';

    public function getEngine(): EngineInterface;

    public function getApplicationDirectory(): string;

    public function getCoroutineContext(): CoroutineContextInterface|null;

    public function getCoroutineScheduler(): CoroutineSchedulerInterface|null;

    /**
     * Return current request env if exists.
     */
    public function getRequestEnvironment(): RequestEnvironmentInterface|null;

    public function setRequestEnvironment(RequestEnvironmentInterface $requestEnvironment): void;

    public function isDeveloperMode(): bool;

    public function isTestMode(): bool;

    public function isWebServer(): bool;

    public function isJobProcess(): bool;

    /**
     * @return string[]
     */
    public function getExecutionRoles(): array;

    /**
     * @return string[]
     */
    public function getRuntimeTags(): array;

    public function isRoleWebServer(): bool;

    public function isRoleJobsServer(): bool;
}
