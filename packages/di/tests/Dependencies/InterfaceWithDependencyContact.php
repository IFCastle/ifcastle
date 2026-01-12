<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

use IfCastle\DI\DependencyContract;

#[DependencyContract(new DependencyProvider())]
interface InterfaceWithDependencyContact
{
    public function someMethod(): void;
}
