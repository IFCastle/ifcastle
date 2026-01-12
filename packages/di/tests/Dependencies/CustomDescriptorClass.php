<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

final readonly class CustomDescriptorClass
{
    public function __construct(
        #[CustomDescriptor] private UseConstructorInterface $some
    ) {}

    public function test(): void
    {
        $this->some->someMethod();
    }
}
