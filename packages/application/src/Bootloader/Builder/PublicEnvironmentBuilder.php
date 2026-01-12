<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\Builder;

use IfCastle\Application\Environment\PublicEnvironment;
use IfCastle\DI\ContainerBuilder;
use IfCastle\DI\ContainerInterface;
use IfCastle\DI\ResolverInterface;

class PublicEnvironmentBuilder extends ContainerBuilder implements PublicEnvironmentBuilderInterface
{
    #[\Override]
    public function buildContainer(
        ResolverInterface $resolver,
        ?ContainerInterface $parentContainer = null,
        bool $isWeakParent = false
    ): ContainerInterface {
        $bindings                   = $this->bindings;
        $this->bindings             = [];

        return new PublicEnvironment($resolver, $bindings, $parentContainer, $isWeakParent);
    }
}
