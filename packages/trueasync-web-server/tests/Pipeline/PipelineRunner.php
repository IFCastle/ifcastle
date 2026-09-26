<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer\Pipeline;

use IfCastle\Application\ApplicationInterface;
use IfCastle\Application\Runner;

/**
 * A Runner that shows its application while run() is still inside a server engine's start().
 */
final class PipelineRunner extends Runner
{
    public function application(): ?ApplicationInterface
    {
        return $this->application;
    }
}
