<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

final class TypeVoid extends TypeNull
{
    public function __construct(string $name = 'void', bool $isRequired = true)
    {
        parent::__construct($name, $isRequired);
        $this->type                 = TypesEnum::VOID->value;
    }
}
