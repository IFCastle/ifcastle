<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

use IfCastle\DI\Dependency;

readonly class ChildClassWithAttributes extends BasicClassWithAttributes
{
    public function __construct(
        #[Dependency('some')]
        private UseConstructorInterface $some,
        #[Dependency]
        private string $someString,
        #[Dependency]
        private int $someInt = 0,
    ) {
        parent::__construct($some, $someInt);
    }

    #[\Override]
    public function test(): void
    {
        $this->some->someMethod();
    }

    public function getSomeString(): string
    {
        return $this->someString;
    }

    #[\Override]
    public function getSomeInt(): int
    {
        return $this->someInt;
    }
}
