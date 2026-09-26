<?php

declare(strict_types=1);

namespace IfCastle\DI\Dependencies;

final class CircularState1
{
    /**
     * @var list<string>
     */
    public array $items             = [];

    public function __construct(public readonly CircularState2 $two) {}
}
