<?php

declare(strict_types=1);

namespace IfCastle\DI;

interface ConfigModelInterface
{
    public function __construct(ConfigInterface $config);
}
