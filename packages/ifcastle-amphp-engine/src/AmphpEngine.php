<?php

declare(strict_types=1);

namespace IfCastle\Amphp;

use IfCastle\Application\EngineAbstract;

class AmphpEngine extends EngineAbstract
{
    #[\Override]
    public function start(): void {}

    #[\Override]
    public function getEngineName(): string
    {
        return 'amphp/' . PHP_VERSION;
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
