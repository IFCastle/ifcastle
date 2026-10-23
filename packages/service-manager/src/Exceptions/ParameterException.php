<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager\Exceptions;

/**
 * The caller's arguments do not fit the method's parameters: the fault of the caller, not of the service.
 * A protocol answers it as a client error.
 */
abstract class ParameterException extends ServiceException
{
    /**
     * @param string               $parameter    name of the parameter the arguments do not fit
     * @param array<string, mixed> $templateData the exception template and its values
     */
    public function __construct(public readonly string $parameter, array $templateData, ?\Throwable $previous = null)
    {
        parent::__construct($templateData, 0, $previous);
    }
}
