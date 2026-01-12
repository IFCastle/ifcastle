<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

use IfCastle\DI\Dependency;
use IfCastle\DI\FromConfig;

readonly class BasicClassWithAttributes
{
    public function __construct(
        #[Dependency('basic_some')]
        private UseConstructorInterface $some,
        #[FromConfig('basic_some_int')]
        private int $someInt = 0,
    ) {}

    public function test(): void
    {
        $this->some->someMethod();
    }

    public function getSomeInt(): int
    {
        return $this->someInt;
    }
}
