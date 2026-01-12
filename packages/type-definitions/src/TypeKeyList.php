<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DefinitionIsNotValid;

/**
 * List of types with string-key.
 */
class TypeKeyList extends TypeList
{
    public function __construct(
        string              $name,
        DefinitionInterface $itemDefinition,
        bool                $isRequired = true,
        bool                $isNullable = true
    ) {
        parent::__construct($name, $itemDefinition, $isRequired, $isNullable);

        $this->type                 = TypesEnum::KEY_LIST->value;
    }

    #[\Override]
    protected function validateKey(mixed $key): void
    {
        if (!\is_string($key)) {
            throw new DefinitionIsNotValid($this, 'List key should be string');
        }
    }

    #[\Override]
    protected function buildOpenApiSchema(?callable $definitionHandler = null): array
    {
        $schema                     = parent::buildOpenApiSchema();
        $schema['additionalProperties'] = $schema['items'];
        unset($schema['items']);

        return $schema;
    }
}
