<?php

declare(strict_types=1);

namespace IfCastle\Application\WorkerProtocol;

use IfCastle\Application\WorkerProtocol\Exceptions\WorkerCommunicationException;
use IfCastle\ServiceManager\CommandDescriptorInterface;
use IfCastle\ServiceManager\ExecutionContextInterface;
use IfCastle\TypeDefinitions\DefinitionStaticAwareInterface;

/**
 * Remote Procedure Call (RPC) protocol between workers.
 * This protocol enables calling remote application procedures
 * within itself and is based on the premise that different application instances operate:
 * * in separate processes or threads
 * * on separate nodes.
 *
 * Since RPC calls occur within the context of a single application,
 * it allows for serializing and deserializing data with confidence
 * that all classes exist both in the code running remotely and in the code invoking the method.
 *
 */
interface WorkerProtocolInterface
{
    /**
     * @param array<string, mixed> $parameters
     *
     * @throws WorkerCommunicationException
     */
    public function buildWorkerRequest(
        string|CommandDescriptorInterface  $service,
        ?string                            $command      = null,
        array                              $parameters   = [],
        ?ExecutionContextInterface         $context      = null
    ): string;

    /**
     * @param string|array<string,mixed> $request
     *
     * @throws WorkerCommunicationException
     */
    public function parseWorkerRequest(string|array $request): WorkerRequestInterface;

    public function buildWorkerResponse(DefinitionStaticAwareInterface|\Throwable $response): string|null;

    /**
     * @throws WorkerCommunicationException
     */
    public function parseWorkerResponse(string $response): mixed;
}
