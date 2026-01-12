<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader;

interface BootloaderContextRequiredInterface
{
    public function setBootloaderContext(BootloaderContextInterface $bootloaderContext): void;
}
