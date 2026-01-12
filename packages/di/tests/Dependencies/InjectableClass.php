<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

use IfCastle\DI\Dependency;
use IfCastle\DI\InjectableInterface;
use IfCastle\DI\InjectorTrait;
use IfCastle\DI\Lazy;

final class InjectableClass implements InjectableInterface
{
    use InjectorTrait;

    #[Dependency]
    protected UseConstructorInterface $required;

    #[Dependency]
    protected UseConstructorInterface|null $optional;

    #[Dependency] #[Lazy]
    protected UseConstructorInterface $lazy;

    protected string $data = '';
}
