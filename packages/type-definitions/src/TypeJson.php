<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DecodingException;
use IfCastle\TypeDefinitions\Exceptions\EncodingException;
use IfCastle\TypeDefinitions\Value\ValueJson;

class TypeJson extends DefinitionAbstract
{
    protected bool $decodeAsNative  = false;

    public function __construct(string $name = '', bool $isRequired = true, bool $isNullable = false)
    {
        parent::__construct($name, TypesEnum::ARRAY->value, $isRequired, $isNullable);
        $this->setReference('#/components/schemas/json');
    }

    /**
     * Point to decode the array as a native array instead of a ValueJsonArray.
     *
     * @return $this
     */
    public function decodeAsNative(): static
    {
        $this->decodeAsNative       = true;

        return $this;
    }

    #[\Override]
    public function isScalar(): bool
    {
        return false;
    }

    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        if (!\is_array($value)) {
            return false;
        }

        // recursive validate an array as JSON array with simple types
        $stack                      = [$value];

        while ($stack !== []) {
            $currentArray           = \array_pop($stack);

            foreach ($currentArray as $item) {
                if (\is_array($item)) {
                    $stack[]        = $item;
                } elseif (!\is_scalar($item) && !\is_null($item)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @throws EncodingException
     */
    #[\Override]
    public function encode(mixed $data): mixed
    {
        if (\is_array($data)) {
            return $data;
        }

        if ($data instanceof ValueJson) {
            return $data->getValue();
        }

        throw new EncodingException($this, 'Only ValueJson values or array can be encoded');
    }

    /**
     * @throws DecodingException
     */
    #[\Override]
    public function decode(array|int|float|string|bool $data): mixed
    {
        if (\is_string($data)) {
            $data              = $this->jsonDecode($data);
        }

        if (!\is_array($data)) {
            throw new DecodingException($this, 'value is not a json', ['value' => $data]);
        }

        if ($this->decodeAsNative) {
            return $data;
        }

        return new ValueJson($data);
    }

    #[\Override]
    protected function buildOpenApiSchema(?callable $definitionHandler = null): array
    {
        $result                     = parent::buildOpenApiSchema($definitionHandler) + [
            'items' => [
                'oneOf' => [
                    ['type' => 'string'],
                    ['type' => 'integer'],
                    ['type' => 'number'],
                    ['type' => 'boolean'],
                    // Self-reference to the same schema
                    ['$ref' => $this->getReference()],
                ],
            ],
        ];

        // OpenAPI 3.0 does not support nullable items,
        // so we use the nullable property to indicate that the array can be null
        $result['nullable']         = true;

        return $result;
    }

    #[\Override]
    public function canDecodeFromString(): bool
    {
        return true;
    }
}
