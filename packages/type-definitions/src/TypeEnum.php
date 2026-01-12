<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DecodingException;
use IfCastle\TypeDefinitions\Exceptions\DefinitionIsNotValid;
use IfCastle\TypeDefinitions\Exceptions\EncodingException;

class TypeEnum extends DefinitionAbstract
{
    public function __construct(
        string $name,
        protected string $enum,
        bool   $isRequired = true,
        bool   $isNullable = false
    ) {
        if (!\is_subclass_of($this->enum, \BackedEnum::class)) {
            throw new DefinitionIsNotValid($this, 'Enum type should be a subclass of BackedEnum');
        }

        parent::__construct($name, TypesEnum::ENUM->value, $isRequired, $isNullable);
    }


    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        if (\is_subclass_of($value, $this->enum)) {
            return true;
        }

        if (\is_subclass_of($this->enum, \BackedEnum::class)) {
            $enum = $this->enum;
            return $enum::tryFrom($value) !== null;
        }

        return false;
    }

    #[\Override]
    public function isScalar(): bool
    {
        return true;
    }

    /**
     * @throws EncodingException
     */
    #[\Override]
    public function encode(mixed $data): mixed
    {
        if (\is_subclass_of($this->enum, \BackedEnum::class)) {
            return $data->value();
        }

        throw new EncodingException($this, 'Failed to encode enum');
    }

    /**
     * @throws DecodingException
     */
    #[\Override]
    public function decode(float|array|bool|int|string $data): mixed
    {
        if (\is_subclass_of($this->enum, \BackedEnum::class)) {
            $enum = $this->enum;
            return $enum::tryFrom($data) ?? throw new DecodingException($this, 'Failed to decode enum');
        }

        throw new DecodingException($this, 'Failed to decode enum');
    }
}
