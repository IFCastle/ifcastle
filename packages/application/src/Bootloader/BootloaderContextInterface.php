<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader;

use IfCastle\Application\Bootloader\Builder\PublicEnvironmentBuilderInterface;
use IfCastle\Application\Environment\SystemEnvironmentInterface;
use IfCastle\Application\RequestEnvironment\RequestPlanInterface;
use IfCastle\DI\BuilderInterface;
use IfCastle\DI\ConfigInterface;
use IfCastle\DI\ContainerInterface;
use IfCastle\DI\ContainerMutableInterface;

interface BootloaderContextInterface extends ContainerInterface, ContainerMutableInterface
{
    public const string APPLICATION_DIRECTORY = 'applicationDirectory';

    public const string APPLICATION_TYPE = 'applicationType';

    public function getApplicationDirectory(): string;

    public function getApplicationType(): string;

    /**
     * @return string[]
     */
    public function getExecutionRoles(): array;

    /**
     * @return string[]
     */
    public function getRuntimeTags(): array;

    public function isWarmUpEnabled(): bool;

    public function enabledWarmUp(): static;

    public function getApplicationConfig(): ConfigInterface;

    public function getSystemEnvironmentBootBuilder(): BuilderInterface;

    public function getPublicEnvironmentBootBuilder(): PublicEnvironmentBuilderInterface;

    public function getRequestEnvironmentPlan(): RequestPlanInterface;

    public function getSystemEnvironment(): SystemEnvironmentInterface|null;
}
