<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

final readonly class CircularDependencyWrong1
{
    public function __construct(
        private CircularDependencyWrong2 $dependency2,
    ) {
        // Wrong code because it tries to use proxy class
        echo $this->dependency2->getDependency1()::class;
    }

    public function getDependency2(): CircularDependencyWrong2
    {
        return $this->dependency2;
    }
}
