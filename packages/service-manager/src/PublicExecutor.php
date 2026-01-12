<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\DI\ContainerInterface;

class PublicExecutor extends ExecutorAbstract
{
    public function __construct(
        private readonly ServiceLocatorInterface $serviceLocator,
        ?ContainerInterface $systemEnvironment = null,
        ?AccessCheckerInterface $accessChecker = null,
        ?TaskRunnerInterface $taskRunner = null,
        ?ServiceTracerInterface $tracer = null
    ) {
        $this->systemEnvironment    = $systemEnvironment;
        $this->accessChecker        = $accessChecker;
        $this->taskRunner           = $taskRunner;
        $this->tracer               = $tracer;

        $this->initializeInterceptors();
    }

    #[\Override]
    protected function resolveService(string $serviceName): array
    {
        if ($this->serviceLocator instanceof ServiceLocatorPublicInternalInterface) {
            $service                = $this->serviceLocator->findPublicService($serviceName);
        } else {
            $service                = $this->serviceLocator->findService($serviceName);
        }

        if ($service === null) {
            return [null, null];
        }

        return [$service, $this->serviceLocator->getServiceDescriptor($serviceName)];
    }
}
