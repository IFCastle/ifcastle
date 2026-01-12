<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Exceptions;

use IfCastle\Exceptions\BaseException;

class DefinitionBuilderException extends BaseException
{
    protected array $tags           = ['definition', 'builder'];
}
