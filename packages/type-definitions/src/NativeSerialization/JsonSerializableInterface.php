<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\NativeSerialization;

interface JsonSerializableInterface
{
    public function jsonEncode(): string;

    public static function jsonDecode(string $object): static;
}
