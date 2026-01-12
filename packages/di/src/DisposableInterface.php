<?php

declare(strict_types=1);

namespace IfCastle\DI;

interface DisposableInterface
{
    public function dispose(): void;
}
