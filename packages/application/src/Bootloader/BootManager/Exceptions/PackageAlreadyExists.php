<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\BootManager\Exceptions;

final class PackageAlreadyExists extends BootloaderException
{
    public function __construct(string $package, ?\Throwable $previous = null)
    {
        parent::__construct("The package '$package' already exists", 0, $previous);
    }
}
