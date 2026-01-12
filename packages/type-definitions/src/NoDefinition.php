<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

/**
 * Empty definition class.
 */
class NoDefinition extends DefinitionAbstract
{
    private static NoDefinition|null $noDefinition = null;

    public static function get(): self
    {
        if (self::$noDefinition === null) {
            self::$noDefinition     = (new self())->asImmutable();
        }

        return self::$noDefinition;
    }

    #[\Override]
    public function isScalar(): bool
    {
        return false;
    }

    public function __construct()
    {
        parent::__construct('NoDefinition', 'null', false, true);
    }

    #[\Override]
    protected function validateValue(mixed $value): bool
    {
        return false;
    }

    #[\Override]
    public function encode(mixed $data): mixed
    {
        return null;
    }

    #[\Override]
    public function decode(float|array|bool|int|string $data): mixed
    {
        return null;
    }
}
