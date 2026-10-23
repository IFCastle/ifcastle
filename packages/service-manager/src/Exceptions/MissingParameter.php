<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager\Exceptions;

/** A parameter without a default value that the caller did not pass. */
final class MissingParameter extends ParameterException
{
    public function __construct(string $parameter, string $service, string $method)
    {
        parent::__construct($parameter, [
            'template'      => 'Parameter "{parameter}" required by {service}->{method}',
            'parameter'     => $parameter,
            'service'       => $service,
            'method'        => $method,
        ]);
    }
}
