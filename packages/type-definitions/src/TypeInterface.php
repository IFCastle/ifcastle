<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

interface TypeInterface
{
    public function getTypeName(): string;

    public function isRequired(): bool;

    public function isNullable(): bool;

    /**
     * Returns TRUE if a type is scalar: string, int, float, boolean.
     */
    public function isScalar(): bool;

    /**
     * Returns TRUE if empty values like '' or 0 should be converted to NULL.
     */
    public function convertEmptyToNull(): bool;

    public function isEmptyToNull(): bool;

    public function getReference(): string;
}
