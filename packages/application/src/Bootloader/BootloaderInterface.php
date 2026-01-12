<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader;

interface BootloaderInterface
{
    public function buildBootloader(BootloaderExecutorInterface $bootloaderExecutor): void;
}
