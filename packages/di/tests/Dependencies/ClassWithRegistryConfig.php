<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

use IfCastle\DI\ConfigInterface;
use IfCastle\DI\FromRegistry;

final class ClassWithRegistryConfig
{
    public function __construct(
        #[FromRegistry('explicitComponent')] public readonly ?ConfigInterface $explicit = null,
        #[FromRegistry] public readonly ?ConfigInterface $implicit = null
    ) {}
}
