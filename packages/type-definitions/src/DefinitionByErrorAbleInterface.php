<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

interface DefinitionByErrorAbleInterface
{
    public static function definitionByError(Error $error): DefinitionInterface;
}
