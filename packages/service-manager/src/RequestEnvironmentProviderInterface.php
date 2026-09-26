<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\DI\ContainerInterface;

/**
 * An environment that knows the request being handled by the current coroutine.
 * Executors read service parameters marked FromEnv(fromRequestEnv: true) from that request.
 */
interface RequestEnvironmentProviderInterface
{
    /**
     * @return ContainerInterface|null the environment of the current request; null outside a request
     */
    public function getRequestEnvironment(): ContainerInterface|null;
}
