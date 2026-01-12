<?php

declare(strict_types=1);

namespace IfCastle\DI;

interface DependencyInterface
{
    public function getDependencyName(): string;

    /**
     * @return DescriptorInterface[]
     */
    public function getDependencyDescriptors(): array;
}
