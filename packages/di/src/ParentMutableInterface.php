<?php

declare(strict_types=1);

namespace IfCastle\DI;

interface ParentMutableInterface
{
    public function setParentContainer(ContainerInterface $parentContainer): static;

    public function resetParentContainer(): void;
}
