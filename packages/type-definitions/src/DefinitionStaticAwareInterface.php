<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

interface DefinitionStaticAwareInterface
{
    public static function definition(): DefinitionMutableInterface;
}
