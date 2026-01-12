<?php

declare(strict_types=1);

namespace IfCastle\DI;

interface NestedContainerInterface extends ContainerInterface
{
    public function getParentContainer(): ContainerInterface|null;
}
