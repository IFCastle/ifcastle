<?php

declare(strict_types=1);

namespace IfCastle\DI;

interface ConfigAwareInterface
{
    public function obtainConfig(): ConfigInterface|null;
}
