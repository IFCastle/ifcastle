<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

use IfCastle\DI\ConfigSection;

#[ConfigSection('class_with_scalar_dependencies')]
final class ClassWithScalarDependencies
{
    public function __construct(
        public string $string,
        public int $int,
        public float $float,
        public bool $bool,
        public array $array,
        public null|string|int|float $mixed
    ) {}
}
