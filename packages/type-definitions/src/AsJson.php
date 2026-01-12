<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT)]
readonly class AsJson extends Type
{
    public function __construct()
    {
        parent::__construct((new TypeJson())->decodeAsNative());
    }
}
