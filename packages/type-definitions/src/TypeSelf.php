<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

class TypeSelf extends DefinitionAbstract
{
    public function __construct(string $name, bool $isRequired = true, bool $isNullable = false)
    {
        parent::__construct($name, 'self', $isRequired, $isNullable);
    }

    #[\Override]
    public function isScalar(): bool
    {
        return false;
    }

    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        return false;
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
