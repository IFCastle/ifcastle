<?php

declare(strict_types=1);

namespace IfCastle\Application;

use IfCastle\Application\Environment\SystemEnvironmentInterface;

interface ApplicationInterface
{
    public function start(): void;

    /**
     * @param array<callable(SystemEnvironmentInterface, EngineInterface): void> $afterEngineHandlers
     */
    public function defineAfterEngineHandlers(array $afterEngineHandlers): void;

    public function engineStart(): void;

    public function end(): void;

    public function getEngine(): EngineInterface;

    public function getSystemEnvironment(): SystemEnvironmentInterface;

    public function getStartTime(): int;

    public function getAppDir(): string;

    public function getVendorDir(): string;

    public function getServerName(): string;

    public function isDeveloperMode(): bool;

    public function criticalLog(mixed $data): void;
}
