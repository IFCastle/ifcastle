<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DefinitionIsNotValid;

class TypeAllOf extends DefinitionAbstract
{
    /**
     * @var DefinitionInterface[]
     */
    protected array $cases          = [];

    public function __construct(string $name, bool $isRequired = true, bool $isNullable = false)
    {
        parent::__construct($name, 'allOf', $isRequired, $isNullable);
    }

    #[\Override]
    public function isScalar(): bool
    {
        foreach ($this->cases as $enumCase) {
            if (false === $enumCase->isScalar()) {
                return false;
            }
        }

        return true;
    }

    public function describeCase(DefinitionInterface $definition): static
    {
        $this->cases[]              = $definition;

        return $this;
    }

    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        if ($this->cases === []) {
            throw new DefinitionIsNotValid($this, 'Enum types should be not empty');
        }

        foreach ($this->cases as $type) {
            $type->validate($value);
        }

        return false;
    }

    protected function defineEnumCases(): void {}

    /**
     * @return DefinitionInterface[]
     */
    public function getCases(): array
    {
        if ($this->cases === []) {
            $this->defineEnumCases();
        }

        return $this->cases;
    }

    /**
     * @throws DefinitionIsNotValid
     */
    #[\Override]
    public function encode(mixed $data): mixed
    {
        if ($this->cases === []) {
            throw new DefinitionIsNotValid($this, 'Enum types should be not empty');
        }

        foreach ($this->cases as $type) {
            try {
                return $type->encode($data);

            } catch (DefinitionIsNotValid) {
                continue;
            }
        }

        throw new DefinitionIsNotValid($this, 'Enum types are not matched');
    }

    /**
     * @throws DefinitionIsNotValid
     */
    #[\Override]
    public function decode(array|int|float|string|bool $data): mixed
    {
        if ($this->cases === []) {
            throw new DefinitionIsNotValid($this, 'Enum types should be not empty');
        }

        foreach ($this->cases as $type) {
            try {
                $decodedValue            = $type->decode($data);

                if ($type->validate($decodedValue, false) === null) {
                    return $decodedValue;
                }

            } catch (DefinitionIsNotValid) {
                continue;
            }
        }

        throw new DefinitionIsNotValid($this, 'Enum types are not matched');
    }

    #[\Override]
    protected function buildOpenApiSchema(?callable $definitionHandler = null): array
    {
        $array                      = [];

        foreach ($this->cases as $enumCase) {
            $array[]                = $enumCase->toOpenApiSchema($definitionHandler);
        }

        return ['allOf' => $array];
    }
}
