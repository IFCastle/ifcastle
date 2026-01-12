<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\Builder;

use IfCastle\Application\Bootloader\BootloaderExecutorInterface;

interface BootloaderBuilderInterface extends ZeroContextInterface
{
    public function build(): void;

    public function getBootloader(): BootloaderExecutorInterface;
}
