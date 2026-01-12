<?php

declare(strict_types=1);

namespace IfCastle\Application;

abstract class EngineAbstract implements EngineInterface
{
    private EngineRolesEnum $engineRole;

    #[\Override]
    public function defineEngineRole(?EngineRolesEnum $engineRole = null): static
    {
        $this->engineRole           = $engineRole ?? (PHP_SAPI === 'cli' ? EngineRolesEnum::PROCESS : EngineRolesEnum::SERVER);

        return $this;
    }

    #[\Override]
    public function getEngineRole(): EngineRolesEnum
    {
        return $this->engineRole;
    }

    #[\Override]
    public function isServer(): bool
    {
        return $this->engineRole === EngineRolesEnum::SERVER;
    }

    #[\Override]
    public function isProcess(): bool
    {
        return $this->engineRole === EngineRolesEnum::PROCESS;
    }

    #[\Override]
    public function isConsole(): bool
    {
        return $this->engineRole === EngineRolesEnum::CONSOLE;
    }
}
