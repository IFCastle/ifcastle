<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\NativeSerialization;

interface EncodeDecodeInterface
{
    public function encode(mixed $data): mixed;

    /**
     * @param array<mixed>|int|float|string|bool $data
     */
    public function decode(array|int|float|string|bool $data): mixed;

    public function canDecodeFromString(): bool;

    public function canDecodeFromArray(): bool;
}
