<?php

declare(strict_types=1);

namespace IfCastle\DI;

interface ConstructibleInterface
{
    public function getClassName(): string;

    public function useConstructor(): bool;
}
