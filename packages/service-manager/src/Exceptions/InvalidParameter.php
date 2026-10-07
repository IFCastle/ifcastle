<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager\Exceptions;

/** Invalid serialized input, distinct from errors raised by the service implementation. */
final class InvalidParameter extends ServiceException
{
    public function __construct(string $parameter, ?\Throwable $previous = null)
    {
        parent::__construct(['template' => 'Invalid value for parameter "{parameter}"', 'parameter' => $parameter], 0, $previous);
    }
}
