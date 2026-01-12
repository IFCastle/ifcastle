<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Value;

use IfCastle\TypeDefinitions\DefinitionInterface;
use IfCastle\TypeDefinitions\DefinitionMutableInterface;
use IfCastle\TypeDefinitions\TypeMixed;

/**
 * Basic implementation of DTO interface.
 *
 */
class ValueContainer implements ValueContainerInterface
{
    #[\Override]
    public static function definition(): DefinitionMutableInterface
    {
        return (new TypeMixed('mixed'))->setInstantiableClass(static::class);
    }

    #[\Override]
    public static function instantiate(mixed $value, ?DefinitionInterface $definition = null): static
    {
        /* @phpstan-ignore-next-line */
        return new static($value, $definition);
    }

    public function __construct(protected mixed $value, protected ?DefinitionInterface $definition = null) {}

    #[\Override]
    public function getValue(): mixed
    {
        return $this->value;
    }

    #[\Override]
    public function containerSerialize(): array|string|bool|int|float|null
    {
        return $this->getDefinition()->encode($this->value);
    }

    /**
     * @throws \JsonException
     */
    #[\Override]
    public function containerToString(): string
    {
        $value                      = $this->containerSerialize();

        if (\is_scalar($value)) {
            return (string) $value;
        }

        return \json_encode($value, JSON_THROW_ON_ERROR);
    }

    #[\Override]
    public function getDefinition(): DefinitionInterface
    {
        if ($this->definition === null) {
            $this->definition       = $this->defineDefinition();
        }

        return $this->definition;
    }

    protected function defineDefinition(): DefinitionInterface
    {
        return static::definition();
    }
}
