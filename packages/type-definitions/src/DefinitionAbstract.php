<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DecodingException;
use IfCastle\TypeDefinitions\Exceptions\DefinitionIsNotValid;
use IfCastle\TypeDefinitions\Exceptions\DescribeException;
use IfCastle\TypeDefinitions\Exceptions\ParseException;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableValidatorInterface;

abstract class DefinitionAbstract implements DefinitionMutableInterface
{
    use AttributesMutableTrait;

    public static function getDefinitionByNativeType(string $name, mixed $value): ?DefinitionMutableInterface
    {
        return match (true) {
            $value === null          => new TypeNull($name),
            \is_bool($value)         => new TypeBool($name),
            \is_string($value)       => new TypeString($name),
            \is_int($value)          => new TypeInteger($name),
            \is_float($value)        => new TypeFloat($name),
            \is_array($value)        => (new TypeJson($name))->decodeAsNative(),
            default                  => null,
        };
    }

    public static function getDefinitionByNativeTypeName(string $typeName, string $name): ?DefinitionMutableInterface
    {
        return match ($typeName) {
            TypesEnum::NULL->value      => new TypeNull($name),
            TypesEnum::VOID->value      => new TypeVoid($name),
            TypesEnum::BOOL->value      => new TypeBool($name),
            TypesEnum::STRING->value    => new TypeString($name),
            TypesEnum::INTEGER->value   => new TypeInteger($name),
            TypesEnum::FLOAT->value     => new TypeFloat($name),
            TypesEnum::ARRAY->value     => (new TypeJson($name))->decodeAsNative(),
            default                     => null,
        };
    }

    /**
     * @throws ParseException
     * @return array<mixed>
     */
    public static function jsonToArray(mixed $value): array
    {
        try {
            if (\is_string($value)) {
                $value              = \json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            }
        } catch (\JsonException $jsonException) {
            throw new ParseException('Value must be a valid JSON string', 0, $jsonException);
        }

        if (!\is_array($value)) {
            throw new ParseException([
                'template'          => 'Value must be an array, got {type} instead.',
                'type'              => \get_debug_type($value),
            ]);
        }

        return $value;
    }

    protected ?string $encodeKey    = null;

    protected bool $isEmptyToNull   = false;

    protected string $description   = '';

    /**
     * The name of the class that can instantiate an object from the raw-data.
     */
    protected string $instantiableClass = '';

    /**
     * Used for OpenAPI reference.
     */
    protected string $reference     = '';

    /**
     * @var object[]
     */
    protected array $attributes     = [];

    protected bool $isDefaultValueAvailable = false;

    protected mixed $defaultValue   = null;

    protected mixed $resolver       = null;

    private bool $isImmutable       = false;

    public function __construct(protected string $name, protected string $type, protected bool $isRequired = true, protected bool $isNullable = false) {}

    public function __clone(): void
    {
        $this->isImmutable          = false;
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[\Override]
    public function setName(string $name): static
    {
        $this->throwIfImmutable();
        $this->name                 = $name;
        return $this;
    }

    #[\Override]
    public function getEncodeKey(): ?string
    {
        return $this->encodeKey;
    }

    #[\Override]
    public function setEncodeKey(?string $encodeKey = null): static
    {
        $this->throwIfImmutable();
        $this->encodeKey            = $encodeKey;
        return $this;
    }

    #[\Override]
    public function getTypeName(): string
    {
        return $this->type;
    }

    #[\Override]
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     *
     * @return $this
     * @throws DescribeException
     */
    #[\Override]
    public function setIsRequired(bool $isRequired): static
    {
        $this->throwIfImmutable();
        $this->isRequired           = $isRequired;
        return $this;
    }

    #[\Override]
    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    /**
     * @return $this
     */
    #[\Override]
    public function setIsNullable(bool $isNullable): static
    {
        $this->throwIfImmutable();
        $this->isNullable           = $isNullable;

        return $this;
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[\Override]
    public function isDefaultValueAvailable(): bool
    {
        return $this->isDefaultValueAvailable;
    }

    #[\Override]
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     *
     * @return $this
     * @throws DescribeException
     */
    #[\Override]
    public function setDescription(string $description): static
    {
        $this->throwIfImmutable();
        $this->description          = $description;

        return $this;
    }

    #[\Override]
    public function convertEmptyToNull(): bool
    {
        return $this->isEmptyToNull;
    }

    /**
     *
     * @return $this
     * @throws DescribeException
     */
    #[\Override]
    public function setEmptyToNull(bool $isEmptyToNull): static
    {
        $this->throwIfImmutable();
        $this->isEmptyToNull        = $isEmptyToNull;

        return $this;
    }

    #[\Override]
    public function getReference(): string
    {
        return $this->reference;
    }

    #[\Override]
    public function getResolver(): callable|null
    {
        return $this->resolver;
    }

    /**
     * @throws DescribeException
     */
    #[\Override]
    public function setReference(string $reference): static
    {
        $this->throwIfImmutable();
        $this->reference            = $reference;

        return $this;
    }

    /**
     * @throws DescribeException
     */
    #[\Override]
    public function asReference(): static
    {
        $this->throwIfImmutable();
        return $this->setReference('#/components/schemas/' . $this->getName());
    }

    #[\Override]
    public function isEmptyToNull(): bool
    {
        return $this->isEmptyToNull;
    }

    /**
     * @throws DescribeException
     */
    #[\Override]
    public function setIsEmptyToNull(bool $isEmptyToNull): static
    {
        $this->throwIfImmutable();
        $this->isEmptyToNull        = $isEmptyToNull;
        return $this;
    }

    #[\Override]
    public function setDefaultValue(mixed $defaultValue): static
    {
        $this->throwIfImmutable();
        $this->isDefaultValueAvailable = true;
        $this->defaultValue           = $defaultValue;
        return $this;
    }

    #[\Override]
    public function resetDefaultValue(): static
    {
        $this->throwIfImmutable();
        $this->isDefaultValueAvailable = false;
        $this->defaultValue           = null;
        return $this;
    }

    #[\Override]
    public function setResolver(callable $resolver): static
    {
        $this->throwIfImmutable();
        $this->resolver             = $resolver;
        return $this;
    }

    public function getInstantiableClass(): string
    {
        return $this->instantiableClass;
    }

    /**
     * @throws DescribeException
     */
    public function setInstantiableClass(string $instantiableClass): static
    {
        $this->throwIfImmutable();
        $this->instantiableClass    = $instantiableClass;
        return $this;
    }

    /**
     * Method that validates the parameter.
     *
     *
     * @throws DefinitionIsNotValid
     */
    #[\Override]
    public function validate(mixed $value, bool $isThrow = true): ?\Throwable
    {
        $error                      = null;

        if (($this->isNullable || !$this->isRequired) && $value === null) {
            return null;
        }

        // Convert empty string as NULL
        if ($this->isEmptyToNull && $value === '') {
            $value                  = null;
        }

        if ($this->isRequired && !$this->isNullable && $value === null) {
            $error                  = new DefinitionIsNotValid($this, \sprintf('Definition \'%s\' cannot be NULL', $this->name));
        } elseif ($value !== null && false === $this->validateValue($value)) {
            $error                  = new DefinitionIsNotValid($this, $this->getErrorMessageForValidate($value));
        }

        if ($error === null) {
            return null;
        }

        if ($isThrow) {
            throw $error;
        }

        return $error;
    }

    #[\Override]
    public function asImmutable(): static
    {
        $this->isImmutable          = true;
        return $this;
    }

    abstract protected function validateValue(mixed $value): bool;

    protected function getErrorMessageForValidate(mixed $value): string
    {
        return \sprintf('Definition \'%s\' does not match type \'%s\'', $this->name, $this->type);
    }

    /**
     * @throws DecodingException
     * @return array<mixed>
     */
    protected function jsonDecode(string $value): array
    {
        $result                     = \json_decode($value, true);

        if (!\is_array($result)) {
            throw new DecodingException($this, 'value is not a json: ' . \json_last_error_msg(), ['value' => $value]);
        }

        return $result;
    }

    #[\Override]
    public function canDecodeFromString(): bool
    {
        return false;
    }

    #[\Override]
    public function canDecodeFromArray(): bool
    {
        return false;
    }

    #[\Override]
    public function toArray(?ArraySerializableValidatorInterface $validator = null): array
    {
        return
        [
            'name'                  => $this->name,
            'type'                  => $this->type,
            'is_required'           => $this->isRequired,
            'is_nullable'           => $this->isNullable,
            'description'           => $this->description,
        ];
    }

    #[\Override]
    public static function fromArray(array $array, ?ArraySerializableValidatorInterface $validator = null): static
    {
        /* @phpstan-ignore-next-line */
        // TODO fromArray
    }

    #[\Override]
    public function toOpenApiSchema(?callable $definitionHandler = null): array
    {
        if ($this->reference !== '' && $definitionHandler !== null) {

            $definitionHandler($this);

            return [
                '$ref'              => $this->reference,
            ];
        }

        return $this->buildOpenApiSchema($definitionHandler);
    }

    /**
     *
     * @return array<mixed>
     */
    protected function buildOpenApiSchema(?callable $definitionHandler = null): array
    {
        $array                      = [];

        if ($this->name !== '' && $this->name !== '0') {
            $array['title']         = $this->name;
        }

        $array['type']              = $this->toOpenApiType();
        $array['format']            = $this->toOpenApiFormat();

        if (empty($array['format'])) {
            unset($array['format']);
        }

        if ($this->isNullable) {
            $array['nullable']      = true;
        }

        if ($this instanceof NumberInterface) {
            if ($this->getMinimum() !== null) {
                $array['minimum']   = $this->getMinimum();
            }

            if ($this->getMaximum() !== null) {
                $array['maximum']   = $this->getMaximum();
            }
        }

        if ($this instanceof StringableInterface) {

            if ($this->getMinLength() !== null) {
                $array['minLength'] = $this->getMinLength();
            }

            if ($this->getMaxLength() !== null) {
                $array['maxLength'] = $this->getMaxLength();
            }

            if ($this->getEcmaPattern() !== null) {
                $array['pattern']   = $this->getEcmaPattern();
            }
        }

        return $array;
    }

    protected function toOpenApiType(): string
    {
        return match ($this->type) {
            TypesEnum::NULL->value  => 'null',
            TypesEnum::BOOL->value  => 'boolean',
            TypesEnum::TIMESTAMP->value,
            TypesEnum::INTEGER->value
                                    => 'integer',
            TypesEnum::FLOAT->value => 'number',

            TypesEnum::OBJECT->value, 'key_list'    => 'object',
            TypesEnum::ARRAY->value, 'list'         => 'array',
            default                 => 'string'
        };
    }

    protected function toOpenApiFormat(): string
    {
        return match ($this->type) {
            TypesEnum::INTEGER->value      => 'int32',
            TypesEnum::TIMESTAMP->value    => 'timestamp',
            TypesEnum::FLOAT->value        => 'float',

            TypesEnum::DATE->value         => 'date',
            TypesEnum::TIME->value         => 'time',
            TypesEnum::UUID->value         => 'guid',

            default                         => ''
        };
    }

    /**
     * @throws DescribeException
     */
    #[\Override]
    protected function throwIfImmutable(): void
    {
        if ($this->isImmutable) {
            throw new DescribeException('definition is immutable', $this);
        }
    }
}
