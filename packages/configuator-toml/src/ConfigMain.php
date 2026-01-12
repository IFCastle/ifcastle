<?php

declare(strict_types=1);

namespace IfCastle\Configurator\Toml;

final class ConfigMain extends ConfigToml
{
    public function __construct(string $appDir)
    {
        parent::__construct($appDir . '/main.toml');
    }
}
