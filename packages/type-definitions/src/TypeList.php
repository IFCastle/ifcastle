<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DefinitionIsNotValid;
use IfCastle\TypeDefinitions\Exceptions\DefinitionTypeInvalid;

/**
 * List of same class types.
 * Array - list of diff types.
 */
class TypeList extends DefinitionAbstract
{
    protected bool $decodeAsNative   = false;

    public function __construct(string $name, protected DefinitionInterface $itemDefinition, bool $isRequired = true, bool $isNullable = false)
    {
        parent::__construct($name, TypesEnum::LIST->value, $isRequired, $isNullable);
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

    /**
     * @throws DefinitionIsNotValid
     */
    protected function validateKey(mixed $key): void
    {
        if (!\is_int($key)) {
            throw new DefinitionIsNotValid($this, 'List key should be number');
        }
    }

    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        if (!\is_array($value)) {
            return false;
        }

        foreach ($value as $key => $item) {

            $this->validateKey($key);

            if ($this->itemDefinition->validate($item, false) !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws DefinitionTypeInvalid
     */
    #[\Override]
    public function decode(array|int|float|string|bool $data): mixed
    {
        if (\is_string($data)) {
            $data              = $this->jsonDecode($data);
        }

        if (!\is_array($data)) {
            throw new DefinitionTypeInvalid($this);
        }

        $decoded                = [];

        foreach ($data as $key => $item) {
            $decoded[$key]      = $this->itemDefinition->decode($item);
        }

        return $decoded;
    }

    #[\Override]
    public function encode(mixed $data): mixed
    {
        return $data;
    }

    #[\Override]
    protected function buildOpenApiSchema(?callable $definitionHandler = null): array
    {
        return parent::buildOpenApiSchema($definitionHandler) + [
            'items' => $this->itemDefinition->toOpenApiSchema($definitionHandler),
        ];
    }
}
