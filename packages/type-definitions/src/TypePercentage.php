<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

class TypePercentage extends TypeInteger
{
    protected int|null $maximum     = 100;

    protected int|null $minimum     = 0;
}
