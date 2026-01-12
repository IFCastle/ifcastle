<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\NativeSerialization;

/**
 * Validator for ArraySerializableI.
 */
interface ArraySerializableValidatorInterface
{
    public function isSerializationAllowed(?object $object = null): bool;

    public function isUnSerializationAllowed(string $type): bool;
}
