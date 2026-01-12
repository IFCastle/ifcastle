<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

class TypeYear extends TypeInteger
{
    public function __construct(string $name, bool $isRequired = true, bool $isNullable = false)
    {
        parent::__construct($name, $isRequired, $isNullable);

        $this->type                 = TypesEnum::YEAR->value;
    }

    #[\Override]
    public function getPattern(): string|null
    {
        return '\d{4}';
    }

    #[\Override]
    public function getUriPattern(): string|null
    {
        return $this->getPattern();
    }

    #[\Override]
    public function getEcmaPattern(): string|null
    {
        return '\\d{4}';
    }
}
