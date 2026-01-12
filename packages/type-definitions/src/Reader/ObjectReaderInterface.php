<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Reader;

use IfCastle\TypeDefinitions\ObjectInterface;

interface ObjectReaderInterface
{
    public function extractObjectDescriptor(string|object $object): ObjectInterface;
}
