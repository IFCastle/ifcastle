<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DefinitionIsNotValid;

/**
 * Select one from many with singe type
 * Compare: Enum: one of many types.
 *
 */
class TypeOptions extends DefinitionAbstract implements StringableInterface
{
    /**
     * @param array<mixed> $variants
     * @throws DefinitionIsNotValid
     */
    public function __construct(
        string                               $name,
        protected DefinitionMutableInterface & StringableInterface $option,
        protected array                      $variants,
        bool                                 $isRequired = true,
        bool                                 $isNullable = false
    ) {
        parent::__construct($name, TypesEnum::OPTIONS->value, $isRequired, $isNullable);

        if ($this->variants === []) {
            throw new DefinitionIsNotValid($this, 'Variants empty');
        }
    }

    #[\Override]
    public function isBinary(): bool
    {
        return false;
    }

    #[\Override]
    public function getMaxLength(): int|null
    {
        // max length of the longest variant
        return \max(\array_map('strlen', $this->variants));
    }

    #[\Override]
    public function getMinLength(): int|null
    {
        // min length of the shortest variant
        return \min(\array_map('strlen', $this->variants));
    }

    #[\Override]
    public function getPattern(): string|null
    {
        return \implode(
            '|', \array_map(static fn($item) => \preg_quote((string) $item), $this->variants)
        );
    }

    #[\Override]
    public function getUriPattern(): string|null
    {
        return $this->getPattern();
    }

    #[\Override]
    public function getEcmaPattern(): string|null
    {
        return \implode(
            '|', \array_map(static fn($item) => \preg_quote((string) $item), $this->variants)
        );
    }

    #[\Override]
    public function isScalar(): bool
    {
        return true;
    }

    /**
     * @return array<mixed>
     */
    public function getVariants(): array
    {
        return $this->variants;
    }

    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        return $this->option->validate($value, false) === null;
    }

    #[\Override]
    public function encode(mixed $data): mixed
    {
        return $data;
    }

    #[\Override]
    public function decode(mixed $data): mixed
    {
        return $this->option->decode($data);
    }

    #[\Override]
    protected function buildOpenApiSchema(?callable $definitionHandler = null): array
    {
        return parent::buildOpenApiSchema($definitionHandler) + ['enum' => $this->variants];
    }

    #[\Override]
    public function canDecodeFromString(): bool
    {
        return $this->option->canDecodeFromString();
    }
}
