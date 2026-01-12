<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

final readonly class ClassWithDependencyContact
{
    public function __construct(
        private InterfaceWithDependencyContact $some
    ) {}

    public function test(): void
    {
        $this->some->someMethod();
    }
}
