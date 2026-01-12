<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

final readonly class CircularDependency1
{
    public function __construct(
        private CircularDependency2 $dependency2,
    ) {}

    public function getDependency2(): CircularDependency2
    {
        return $this->dependency2;
    }
}
