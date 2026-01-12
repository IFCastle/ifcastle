<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DecodingException;
use IfCastle\TypeDefinitions\Exceptions\EncodingException;
use IfCastle\TypeDefinitions\Value\ValueUlid;

class TypeUlid extends TypeString
{
    public const string PREG_ULID   = '/^[0-9A-HJ-KM-NP-TV-Z]{26}$/';

    public function __construct(string $name, bool $isRequired = true, bool $isNullable = false)
    {
        parent::__construct($name, $isRequired, $isNullable);

        $this->type                 = TypesEnum::ULID->value;
    }

    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        return parent::validateValue($value) && \preg_match(self::PREG_ULID, (string) $value);
    }

    #[\Override]
    public function decode(array|int|float|string|bool $data): mixed
    {
        if (!\is_string($data) || !\preg_match(self::PREG_ULID, $data)) {
            throw new DecodingException($this, 'Expected string with ULID format');
        }

        return new ValueUlid(parent::decode($data));
    }

    /**
     * @throws EncodingException
     */
    #[\Override]
    public function encode(mixed $data): mixed
    {
        if (\is_string($data)) {
            return $data;
        }

        if ($data instanceof ValueUlid) {
            return $data->getValue();
        }

        throw new EncodingException($this, 'Expected ValueUlid type or string');
    }
}
