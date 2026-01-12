<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager\Exceptions;

class ServiceNotFound extends ServiceException
{
    protected array $tags           = ['service'];

    /**
     * @param array<scalar|null|scalar[]> $debugData
     */
    public function __construct(string $service, array $debugData = [])
    {
        parent::__construct([
            'template'              => 'Service {service} not found',
            'service'               => $service,
        ]);

        $this->setDebugData($debugData);
    }
}
