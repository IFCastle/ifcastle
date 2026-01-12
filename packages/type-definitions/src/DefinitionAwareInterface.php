<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

interface DefinitionAwareInterface
{
    public function getDefinition(): DefinitionInterface;
}
