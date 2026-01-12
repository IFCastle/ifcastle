<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use Attribute;
use IfCastle\TypeDefinitions\NativeSerialization\AttributeNameInterface;

#[Attribute(Attribute::TARGET_CLASS)]
final class AsPublicService implements AttributeNameInterface
{
    #[\Override]
    public function getAttributeName(): string
    {
        return self::class;
    }
}
