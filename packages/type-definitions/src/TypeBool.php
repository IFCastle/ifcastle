<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DecodingException;
use IfCastle\TypeDefinitions\Exceptions\DefinitionIsNotValid;
use IfCastle\TypeDefinitions\Exceptions\EncodingException;
use IfCastle\TypeDefinitions\Value\ValueBool;

class TypeBool extends DefinitionAbstract implements StringableInterface
{
    public function __construct(string $name, bool $isRequired = true, bool $isNullable = false)
    {
        parent::__construct($name, TypesEnum::BOOL->value, $isRequired, $isNullable);
    }

    #[\Override]
    public function isScalar(): bool
    {
        return true;
    }

    #[\Override]
    public function isBinary(): bool
    {
        return false;
    }

    #[\Override]
    public function getMaxLength(): int|null
    {
        return 5;
    }

    #[\Override]
    public function getMinLength(): int|null
    {
        return 4;
    }

    #[\Override]
    public function getPattern(): string|null
    {
        return '(true|false|1|0)';
    }

    #[\Override]
    public function getUriPattern(): string|null
    {
        return $this->getPattern();
    }

    #[\Override]
    public function getEcmaPattern(): string|null
    {
        return '(true|false|1|0)';
    }

    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        return $value === true || $value === false;
    }

    /**
     * @throws DefinitionIsNotValid
     */
    #[\Override]
    public function decode(array|int|float|string|bool $data): bool
    {
        if (\is_string($data)) {
            $data                   = \strtolower($data);
        }

        return match ($data) {
            true, 1, 'true'         => true,
            false, 0, 'false'       => false,
            default                 => throw new DecodingException($this, 'Invalid boolean format', ['data' => $data])
        };
    }

    #[\Override]
    public function encode(mixed $data): mixed
    {
        if (\is_bool($data)) {
            return $data;
        }

        if ($data instanceof ValueBool) {
            return $data->getValue();
        }

        throw new EncodingException($this, 'Expected type bool', ['data' => $data]);
    }

    #[\Override]
    public function canDecodeFromString(): bool
    {
        return true;
    }
}
