<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\NativeSerialization;

interface ArraySerializableInterface
{
    /**
     * @return array<mixed>
     */
    public function toArray(?ArraySerializableValidatorInterface $validator = null): array;

    /**
     * @param array<mixed> $array
     */
    public static function fromArray(array $array, ?ArraySerializableValidatorInterface $validator = null): static;
}
