<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

class TypeBinary extends TypeString
{
    #[\Override]
    public function isBinary(): bool
    {
        return true;
    }

    #[\Override]
    protected function toOpenApiFormat(): string
    {
        return 'binary';
    }
}
