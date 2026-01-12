<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

interface NumberMutableInterface extends NumberInterface, DefinitionMutableInterface
{
    public function setMinimum(int|float $minimum): static;

    public function setMaximum(int|float $maximum): static;
}
