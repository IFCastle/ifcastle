<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

final class CircularState2
{
    public function __construct(public readonly CircularState1 $one) {}
}
