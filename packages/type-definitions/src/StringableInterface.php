<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

interface StringableInterface extends TypeInterface
{
    public function isBinary(): bool;

    public function getMaxLength(): int|null;

    public function getMinLength(): int|null;

    public function getPattern(): string|null;

    public function getUriPattern(): string|null;

    public function getEcmaPattern(): string|null;
}
