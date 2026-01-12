<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

final class ObjectWithDependencyContact implements InterfaceWithDependencyContact
{
    #[\Override]
    public function someMethod(): void {}
}
