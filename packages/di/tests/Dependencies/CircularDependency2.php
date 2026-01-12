<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

final readonly class CircularDependency2
{
    public function __construct(
        private CircularDependency1 $dependency1,
    ) {}

    public function getDependency1(): CircularDependency1
    {
        return $this->dependency1;
    }
}
