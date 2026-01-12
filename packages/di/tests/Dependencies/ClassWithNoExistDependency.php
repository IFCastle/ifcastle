<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

class ClassWithNoExistDependency
{
    public function __construct(
        public NoExistDependency $noExistDependency
    ) {}
}
