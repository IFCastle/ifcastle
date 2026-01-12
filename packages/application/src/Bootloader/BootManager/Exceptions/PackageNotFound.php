<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\BootManager\Exceptions;

final class PackageNotFound extends BootloaderException
{
    public function __construct(string $package, ?\Throwable $previous = null)
    {
        parent::__construct("The package '$package' is not found", 0, $previous);
    }
}
