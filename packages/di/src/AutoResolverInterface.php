<?php

declare(strict_types=1);

namespace IfCastle\DI;

interface AutoResolverInterface
{
    public function resolveDependencies(ContainerInterface $container): void;
}
