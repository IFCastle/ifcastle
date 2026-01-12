<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DecodingException;
use IfCastle\TypeDefinitions\Exceptions\EncodingException;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableInterface;
use IfCastle\TypeDefinitions\Value\InstantiateInterface;
use IfCastle\TypeDefinitions\Value\ValueObject;

class TypeObject extends DefinitionAbstract implements ObjectInterface
{
    /**
     * @var DefinitionInterface[]
     */
    protected array $properties     = [];

    public function __construct(string $name, bool $isRequired = true, bool $isNullable = false)
    {
        parent::__construct($name, TypesEnum::OBJECT->value, $isRequired, $isNullable);
    }

    #[\Override]
    public function isScalar(): bool
    {
        return false;
    }

    public function describe(DefinitionInterface $definition, ?string $key = null): static
    {
        if ($key === null) {
            $key                    = $definition->getEncodeKey() ?? $definition->getName();
        }

        $this->properties[$key]     = $definition;

        return $this;
    }

    #[\Override]
    public function getProperties(): array
    {
        return $this->properties;
    }

    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        return $value instanceof ValueObject;
    }

    /**
     * @throws DecodingException
     * @throws \JsonException
     */
    #[\Override]
    public function decode(array|int|float|string|bool $data): mixed
    {
        if (\is_string($data)) {
            $data                  = \json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        }

        if (false === \is_array($data)) {
            return $data;
        }

        $instantiableClass      = $this->instantiableClass !== '' ? $this->instantiableClass : ValueObject::class;

        if (!\class_exists($instantiableClass)) {
            throw new DecodingException($this, 'instantiable class not exists', ['value' => $instantiableClass]);
        }

        $unknownProperties      = \array_diff(\array_keys($data), \array_keys($this->properties));

        if ($unknownProperties !== []) {
            throw new DecodingException($this, 'Unknown properties', ['properties' => $unknownProperties]);
        }

        $decodedData            = [];

        foreach ($this->properties as $key => $property) {

            if (false === \array_key_exists($key, $data)) {

                if ($property->isNullable() && $property->isRequired()) {
                    $decodedData[$property->getName()] = null;
                }

                if ($property->isRequired()) {
                    throw new DecodingException($this, 'Required property not found', ['property' => $key]);
                }

                continue;
            }

            if ($data[$key] === null && false === $property->isNullable()) {
                throw new DecodingException($this, 'Property is not nullable', ['property' => $key]);
            }

            $decodedData[$property->getName()] = $property->decode($data[$key]);
        }

        if (\is_subclass_of($instantiableClass, InstantiateInterface::class)) {
            return $instantiableClass::instantiate($decodedData, $this);
        }

        return new $instantiableClass(...$decodedData);

    }

    /**
     * @throws EncodingException
     */
    #[\Override]
    public function encode(mixed $data): mixed
    {
        $notEncoded                 = true;

        if ($data instanceof ArraySerializableInterface) {
            $notEncoded             = false;
            $data                   = $data->toArray();
        }

        if (!\is_array($data)) {
            throw new EncodingException($this, 'Only array values can be encoded', ['value' => $data]);
        }

        $encoded                    = [];

        foreach ($this->properties as $key => $property) {

            $propertyName           = $property->getName();

            if (false === \array_key_exists($propertyName, $data)) {

                if ($property->isNullable() && $property->isRequired()) {
                    $encoded[$key]  = null;
                }

                if ($property->isRequired()) {
                    throw new EncodingException($this, 'Required property not found', ['property' => $key]);
                }

                continue;
            }

            if ($data[$propertyName] === null && false === $property->isNullable()) {
                throw new EncodingException($this, 'Property is not nullable', ['property' => $key]);
            }

            /* @phpstan-ignore-next-line */
            $encoded[$key]      = $notEncoded ? $property->encode($data[$propertyName]) : $data[$propertyName];
        }

        return $encoded;
    }

    #[\Override]
    protected function buildOpenApiSchema(?callable $definitionHandler = null): array
    {
        $specification              = parent::buildOpenApiSchema($definitionHandler) +
            [
                'properties'        => $this->toOpenApiProperties($definitionHandler),
            ];

        $required                   = $this->toOpenApiRequired();

        if ($required !== []) {
            $specification['required'] = $required;
        }

        return $specification;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function toOpenApiProperties(?callable $definitionHandler = null): array
    {
        $properties                 = [];

        foreach ($this->properties as $key => $property) {
            $properties[$key]       = $property->toOpenApiSchema($definitionHandler);
        }

        return $properties;
    }

    /**
     * @return string[]
     */
    public function toOpenApiRequired(): array
    {
        $required                   = [];

        foreach ($this->properties as $key => $property) {
            if ($property->isRequired()) {
                $required[]         = $key;
            }
        }

        return $required;
    }

    #[\Override]
    public function canDecodeFromString(): bool
    {
        return true;
    }

    #[\Override]
    public function canDecodeFromArray(): bool
    {
        return true;
    }
}
