<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\Builder;

use IfCastle\Application\Environment\SystemEnvironment;
use IfCastle\DI\ContainerBuilder;
use IfCastle\DI\ContainerInterface;
use IfCastle\DI\ResolverInterface;

class SystemEnvironmentBuilder extends ContainerBuilder
{
    #[\Override]
    public function buildContainer(
        ResolverInterface $resolver,
        ?ContainerInterface $parentContainer = null,
        bool $isWeakParent = false
    ): ContainerInterface {
        $bindings                   = $this->bindings;
        $this->bindings             = [];

        return new SystemEnvironment($resolver, $bindings, $parentContainer, $isWeakParent);
    }
}
