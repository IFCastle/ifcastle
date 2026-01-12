<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Exceptions;

use IfCastle\Exceptions\RuntimeException;

class ParseException extends RuntimeException
{
    protected array $tags           = ['request', 'protocol'];
}
