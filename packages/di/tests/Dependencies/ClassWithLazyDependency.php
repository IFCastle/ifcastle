<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

use IfCastle\DI\Lazy;

class ClassWithLazyDependency
{
    public function __construct(
        #[Lazy]
        public UseConstructorInterface $some,
    ) {}
}
