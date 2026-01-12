<?php

declare(strict_types=1);

namespace IfCastle\Application\Bootloader\Builder;

interface ZeroContextRequiredInterface
{
    public function setZeroContext(ZeroContextInterface $zeroContext): static;
}
