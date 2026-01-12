<?php

declare(strict_types=1);

namespace IfCastle\DI;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ConfigSection
{
    public function __construct(public string $section) {}
}
