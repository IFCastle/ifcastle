<?php

declare(strict_types=1);

namespace IfCastle\Application;

enum EngineRolesEnum: string
{
    case SERVER                     = 'server';
    case PROCESS                    = 'process';
    case CONSOLE                    = 'console';
}
