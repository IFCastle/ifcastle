<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

interface NumberInterface extends TypeInterface
{
    public function isUnsigned(): bool;

    public function isNonZero(): bool;

    public function getMinimum(): int|float|null;

    public function getMaximum(): int|float|null;
}
