<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Value;

use IfCastle\Exceptions\UnexpectedValueType;
use IfCastle\TypeDefinitions\DefinitionAbstract;
use IfCastle\TypeDefinitions\DefinitionInterface;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableInterface;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableValidatorInterface;
use IfCastle\TypeDefinitions\NativeSerialization\JsonSerializableInterface;
use IfCastle\TypeDefinitions\TypeObject;

class ValueObject extends ValueContainer implements ArraySerializableInterface, JsonSerializableInterface
{
    public function getObjectProperty(string $name): mixed
    {
        return $this->value[$name] ?? null;
    }

    public function getObjectPropertyAsInt(string $name): int
    {
        $property                   = $this->getObjectProperty($name);

        return $property !== null ? (int) $property : 0;
    }

    public function getObjectPropertyAsBool(string $name): bool
    {
        return (bool) $this->getObjectProperty($name);
    }

    /**
     * The method generates a type definition on the fly from a real value.
     *
     * @throws UnexpectedValueType
     */
    #[\Override]
    protected function defineDefinition(): DefinitionInterface
    {
        if (!\is_array($this->value) && false === $this->value instanceof \Traversable) {
            // 'The value of the container does not match the format of the object (must be an array)'
            throw new UnexpectedValueType(
                '$this->value', $this->value, 'array'
            );
        }

        $definition                 = new TypeObject('Object');

        foreach ($this->value as $key => $value) {

            if (!\is_string($key)) {
                throw new UnexpectedValueType('$key', $key, 'string');
            }

            $type                   = DefinitionAbstract::getDefinitionByNativeType($key, $value);

            if ($type === null) {
                // The value of object should be scalar
                throw new UnexpectedValueType('$type', $type, 'scalar');
            }

            $definition->describe($type);
        }

        return $definition;
    }

    #[\Override]
    public function toArray(?ArraySerializableValidatorInterface $validator = null): array
    {
        return $this->getDefinition()->encode($this->value);
    }

    #[\Override]
    public static function fromArray(array $array, ?ArraySerializableValidatorInterface $validator = null): static
    {
        return static::definition()->decode($array);
    }

    #[\Override]
    public function jsonEncode(): string
    {
        return \json_encode($this->toArray());
    }

    #[\Override]
    public static function jsonDecode(string $object): static
    {
        return static::fromArray(\json_decode($object, true, JSON_THROW_ON_ERROR), null);
    }
}
