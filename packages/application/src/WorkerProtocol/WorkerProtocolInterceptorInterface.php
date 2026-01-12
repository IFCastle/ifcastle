<?php

declare(strict_types=1);

namespace IfCastle\Application\WorkerProtocol;

use IfCastle\Application\WorkerProtocol\Exceptions\WorkerCommunicationException;
use IfCastle\DesignPatterns\Interceptor\InterceptorInterface;
use IfCastle\ServiceManager\CommandDescriptorInterface;
use IfCastle\ServiceManager\ExecutionContextInterface;

/**
 * Interceptor interface that intercepts the operation of the request serializer for the Worker.
 * The interceptor can modify the parameters or completely cancel the execution of the main handler.
 *
 * @template-extends InterceptorInterface<WorkerProtocolInterface>
 */
interface WorkerProtocolInterceptorInterface extends InterceptorInterface
{
    /**
     * @param array<string, mixed> $parameters
     * @throws WorkerCommunicationException
     */
    public function interceptWorkerRequest(
        string|CommandDescriptorInterface  $service,
        ?string                            $command      = null,
        array                              $parameters   = [],
        ?ExecutionContextInterface         $context      = null
    ): void;
}
