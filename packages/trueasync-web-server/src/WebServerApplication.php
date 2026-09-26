<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer;

use IfCastle\Application\ApplicationAbstract;
use IfCastle\Application\EngineRolesEnum;

/**
 * An IFCastle application in the server role, served by WebServerEngine.
 *
 * Start it with the application type APP_TYPE: the package installer registers this package's
 * bootloader for that type only, so under another type the application gets no web server.
 */
class WebServerApplication extends ApplicationAbstract
{
    // Keep equal to extra.ifcastle-installer.package.applications in this package's composer.json.
    public const string APP_TYPE    = 'server';

    #[\Override]
    protected function defineEngineRole(): EngineRolesEnum
    {
        return EngineRolesEnum::SERVER;
    }
}
