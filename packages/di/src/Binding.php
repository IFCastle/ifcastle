<?php

declare(strict_types=1);

namespace IfCastle\DI;

use Attribute;

/**
 * Indicates that this class is bound to the specified interfaces.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Binding
{
    /**
     * @var string[] $interfaces
     */
    public array $interfaces;

    public function __construct(string ...$interfaces)
    {
        $this->interfaces           = $interfaces;
    }
}
