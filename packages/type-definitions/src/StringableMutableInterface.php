<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

interface StringableMutableInterface extends StringableInterface, DefinitionMutableInterface
{
    public function setMaxLength(int $maxLength): static;

    public function setMinLength(int $minLength): static;

    public function setPattern(string $pattern): static;

    public function setEcmaPattern(string $ecmaPattern): static;
}
