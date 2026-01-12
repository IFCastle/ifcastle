<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DefinitionIsNotValid;

class TypeIntegerOrString extends TypeInteger
{
    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        if (parent::validateValue($value)) {
            return true;
        }

        return \is_string($value);
    }

    /**
     * @throws DefinitionIsNotValid
     */
    #[\Override]
    public function decode(array|int|float|string|bool $data): mixed
    {
        return parent::validateValue($data) ? parent::decode($data) : (string) $data;
    }

    #[\Override]
    public function encode(mixed $data): mixed
    {
        return $data;
    }
}
