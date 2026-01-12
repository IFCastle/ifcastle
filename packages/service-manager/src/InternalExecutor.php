<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\DI\ContainerInterface;

final class InternalExecutor extends ExecutorAbstract
{
    public function __construct(
        private readonly ServiceLocatorInterface $publicLocator,
        private readonly ServiceLocatorInterface $internalLocator,
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
        $service                    = $this->publicLocator->findService($serviceName);

        if ($service !== null) {
            return [$service, $this->publicLocator->getServiceDescriptor($serviceName)];
        }

        $service                    = $this->internalLocator->findService($serviceName);

        if ($service === null) {
            return [null, null];
        }

        return [$service, $this->internalLocator->getServiceDescriptor($serviceName)];
    }
}
