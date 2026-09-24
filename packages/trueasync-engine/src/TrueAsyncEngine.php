<?php

declare(strict_types=1);

namespace IfCastle\TrueAsync;

use IfCastle\Application\EngineAbstract;

/**
 * Engine for applications running on the TrueAsync extension.
 *
 * TrueAsync starts its scheduler on its own, so there is nothing to start here.
 */
class TrueAsyncEngine extends EngineAbstract
{
    #[\Override]
    public function start(): void {}

    #[\Override]
    public function getEngineName(): string
    {
        return 'trueasync/' . PHP_VERSION;
    }

    #[\Override]
    public function isStateful(): bool
    {
        return true;
    }

    #[\Override]
    public function isAsynchronous(): bool
    {
        return true;
    }

    #[\Override]
    public function supportCoroutines(): bool
    {
        return true;
    }
}
