<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

class TypeMixed extends DefinitionAbstract
{
    public function __construct(string $name, bool $isRequired = true, bool $isNullable = false)
    {
        parent::__construct($name, TypesEnum::MIXED->value, $isRequired, $isNullable);
    }

    #[\Override]
    public function isScalar(): bool
    {
        return false;
    }

    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        return true;
    }

    #[\Override]
    public function encode(mixed $data): mixed
    {
        return $data;
    }

    #[\Override]
    public function decode(float|array|bool|int|string $data): mixed
    {
        return $data;
    }
}
