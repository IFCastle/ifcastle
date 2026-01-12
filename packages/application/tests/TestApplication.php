<?php

declare(strict_types=1);

namespace IfCastle\Application;

final class TestApplication extends ApplicationAbstract
{
    #[\Override]
    protected function defineEngineRole(): EngineRolesEnum
    {
        return EngineRolesEnum::CONSOLE;
    }
}
