<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\NativeSerialization;

interface JsonSerializableFromInterface
{
    public function jsonEncodeFrom(mixed $data): string;

    public function jsonDecodeFrom(string $data): mixed;
}
