<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

final readonly class CircularDependencyWrong2
{
    public function __construct(
        private CircularDependencyWrong1 $dependency1,
    ) {
        // Wrong code because it tries to use proxy class
        echo $this->dependency1->getDependency2()::class;
    }

    public function getDependency1(): CircularDependencyWrong1
    {
        return $this->dependency1;
    }
}
