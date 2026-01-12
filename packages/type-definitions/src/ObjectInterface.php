<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

interface ObjectInterface extends DefinitionInterface
{
    /**
     * @return array<string, DefinitionInterface>
     */
    public function getProperties(): array;

    public function getInstantiableClass(): string;
}
