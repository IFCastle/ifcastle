<?php

declare(strict_types=1);

namespace IfCastle\TrueAsync\Exceptions;

use IfCastle\Exceptions\LogicalException;

/**
 * An async-contracts member that trueasync-engine does not implement; code uses the Async\* API directly instead.
 */
final class UnsupportedOperation extends LogicalException
{
    protected string $template      = '{method} is not supported by trueasync-engine; use the Async\* API directly';

    public function __construct(string $method)
    {
        parent::__construct(['method' => $method]);
    }
}
