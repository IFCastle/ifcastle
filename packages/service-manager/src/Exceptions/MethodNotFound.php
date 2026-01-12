<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager\Exceptions;

use IfCastle\Exceptions\ClientException;

class MethodNotFound extends ClientException
{
    protected array $tags           = ['service'];

    public function __construct(string $service, string $method, array $debugData = [])
    {
        parent::__construct('The method {service}.{method} is not found', ['service' => $service, 'method' => $method], $debugData);
    }
}
