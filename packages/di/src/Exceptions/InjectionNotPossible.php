<?php

declare(strict_types=1);

namespace IfCastle\DI\Exceptions;

final class InjectionNotPossible extends \Exception
{
    public function __construct(string|object $object, string $type, string $expected, ?\Throwable $previous = null)
    {
        $object                     = \is_string($object) ? $object : \get_debug_type($object);

        parent::__construct("Type '$type' cannot be used to resolve dependencies for '$object' (expected $expected)", 0, $previous);
    }
}
