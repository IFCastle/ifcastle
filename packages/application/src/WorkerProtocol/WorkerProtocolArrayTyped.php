<?php

declare(strict_types=1);

namespace IfCastle\Application\WorkerProtocol;

use IfCastle\Application\Environment\SystemEnvironmentInterface;
use IfCastle\Application\WorkerProtocol\Exceptions\WorkerCommunicationException;
use IfCastle\DesignPatterns\Interceptor\InterceptorPipeline;
use IfCastle\DesignPatterns\Interceptor\InterceptorRegistryInterface;
use IfCastle\ServiceManager\CommandDescriptorInterface;
use IfCastle\ServiceManager\ExecutionContext;
use IfCastle\ServiceManager\ExecutionContextInterface;
use IfCastle\TypeDefinitions\DefinitionStaticAwareInterface;
use IfCastle\TypeDefinitions\Exceptions\RemoteException;
use IfCastle\TypeDefinitions\NativeSerialization\ArraySerializableInterface;
use IfCastle\TypeDefinitions\NativeSerialization\ArrayTyped;
use IfCastle\TypeDefinitions\Value\ContainerSerializableInterface;

final class WorkerProtocolArrayTyped implements WorkerProtocolInterface
{
    private ?bool $isMsgPackExtensionLoaded = null;

    /**
     * @var array<WorkerProtocolInterceptorInterface>
     */
    protected array $interceptors = [];

    public function __construct(
        protected SystemEnvironmentInterface $systemEnvironment,
        ?InterceptorRegistryInterface $interceptorRegistry = null,
    ) {

        $this->isMsgPackExtensionLoaded = \extension_loaded('msgpack');
        /* @phpstan-ignore-next-line */
        $this->interceptors = $interceptorRegistry?->resolveInterceptors(WorkerProtocolInterceptorInterface::class)
                              ?? [];
    }

    #[\Override]
    public function buildWorkerRequest(
        string|CommandDescriptorInterface $service,
        ?string                           $command      = null,
        array                             $parameters   = [],
        ?ExecutionContextInterface        $context      = null,
    ): string {
        [, $service, $command, $parameters, $context] = (new InterceptorPipeline(
            $this, [__METHOD__, $service, $command, $parameters, $context], ...$this->interceptors,
        ))->getLastArguments();

        if ($service instanceof CommandDescriptorInterface) {

            if ($service instanceof ArraySerializableInterface) {
                $service            = ArrayTyped::serialize($service);
            } else {
                throw new WorkerCommunicationException(
                    'The worker request service is invalid: expected ArraySerializableInterface, got ' . $service::class,
                );
            }
        }

        if ($context === null) {
            $context                = new ExecutionContext();
        }

        $context                    = ArrayTyped::serialize($context);

        // Serialize parameters
        foreach ($parameters as $key => $parameter) {
            if ($parameter instanceof ContainerSerializableInterface) {
                $parameters[$key]   = $parameter->containerSerialize();
            }
        }

        if ($this->isMsgPackExtensionLoaded) {
            try {
                return 'm' . msgpack_pack([$service, $command, $parameters, $context]);
            } catch (\Throwable $exception) {
                throw new WorkerCommunicationException('The msgpack encode error occurred: ' . $exception->getMessage(), 0, $exception);
            }
        }

        try {
            return 'j' . \json_encode([$service, $command, $parameters, $context], JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new WorkerCommunicationException('The json encode error occurred: ' . $exception->getMessage(), 0, $exception);
        }
    }

    #[\Override]
    public function parseWorkerRequest(array|string $request): WorkerRequestInterface
    {
        $data                       = \is_string($request) ? $this->parseIncoming($request) : $request;

        if (\count($data) !== 4) {
            throw new WorkerCommunicationException('The worker request data is invalid: expected 4 elements, got ' . \count($data));
        }

        [$service, $command, $parameters, $context] = $data;

        if (\is_array($service)) {

            try {
                $service                = ArrayTyped::unserialize($service);
            } catch (\Throwable $exception) {
                throw new WorkerCommunicationException('The worker request service is invalid: ' . $exception->getMessage(), 0, $exception);
            }

        } elseif (!\is_string($service)) {
            throw new WorkerCommunicationException('The worker request service is invalid: expected string, got ' . \gettype($service));
        }

        if (!\is_string($command) && !\is_null($command)) {
            throw new WorkerCommunicationException('The worker request command is invalid: expected string, got ' . \gettype($command));
        }

        if (!\is_array($parameters)) {
            throw new WorkerCommunicationException('The worker request parameters is invalid: expected array, got ' . \gettype($parameters));
        }

        if ($context !== null && !\is_array($context)) {
            throw new WorkerCommunicationException('The worker request context is invalid: expected array, got ' . \gettype($context));
        }

        $context                    = ArrayTyped::unserialize($context);

        /* @phpstan-ignore-next-line */
        if (false === $context instanceof ExecutionContextInterface) {
            throw new WorkerCommunicationException(
                'The worker request context is invalid: expected ExecutionContextInterface, got ' . \get_debug_type($context),
            );
        }

        /* @phpstan-ignore-next-line */
        return new WorkerRequest(
            $service instanceof CommandDescriptorInterface ?
                $service : new Command($service, $command, $parameters),
            $context,
        );
    }

    #[\Override]
    public function buildWorkerResponse(DefinitionStaticAwareInterface|\Throwable $response): string|null
    {
        if ($response instanceof DefinitionStaticAwareInterface) {
            $definition             = $response::definition();
            $response               = [$response::class, $definition->encode($response)];
        } elseif ($response instanceof \Throwable) {
            $response               = new RemoteException($response);
            $definition             = $response->getDefinition();
            $response               = [$response::class, $definition->encode($response)];
        } else {
            throw new WorkerCommunicationException(
                'The worker response is invalid: expected ContainerSerializableInterface or Throwable, got ' . \get_debug_type($response),
            );
        }

        if ($this->isMsgPackExtensionLoaded) {
            try {
                return 'm' . msgpack_pack($response);
            } catch (\Throwable $exception) {
                throw new WorkerCommunicationException('The msgpack encode error occurred: ' . $exception->getMessage(), 0, $exception);
            }
        }

        try {
            return 'j' . \json_encode($response, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new WorkerCommunicationException('The json encode error occurred: ' . $exception->getMessage(), 0, $exception);
        }
    }

    #[\Override]
    public function parseWorkerResponse(string $response): mixed
    {
        $data                       = $this->parseIncoming($response);

        if (\count($data) !== 2) {
            throw new WorkerCommunicationException('The worker response data is invalid: expected 2 elements, got ' . \count($data));
        }

        [$class, $response]         = $data;

        if (!\is_string($class)) {
            throw new WorkerCommunicationException('The worker response class is invalid: expected string, got ' . \gettype($class));
        }

        if (\is_subclass_of($class, DefinitionStaticAwareInterface::class)) {
            return $class::definition()->decode($response);
        }

        throw new WorkerCommunicationException(
            'The worker response class is invalid: expected DefinitionStaticAwareInterface, got ' . $class
        );
    }

    /**
     * @return array<mixed>
     * @throws WorkerCommunicationException
     */
    protected function parseIncoming(string $data): array
    {
        if (\str_starts_with($data, 'm')) {
            $isMsgPack              = true;
        } elseif (\str_starts_with($data, 'j')) {
            $isMsgPack              = false;
        } else {
            throw new WorkerCommunicationException('The worker response data is invalid: expected "m" or "j" prefix, got ' . $data[0]);
        }

        if ($isMsgPack && !$this->isMsgPackExtensionLoaded) {
            throw new WorkerCommunicationException('The msgpack extension is not loaded but the worker data is encoded with msgpack');
        }

        $data                       = \substr($data, 1);

        if ($this->isMsgPackExtensionLoaded) {
            try {
                return msgpack_unpack($data);
            } catch (\Throwable $exception) {
                throw new WorkerCommunicationException('The msgpack decode error occurred: ' . $exception->getMessage(), 0, $exception);
            }
        }

        try {
            return \json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new WorkerCommunicationException('The json decode error occurred: ' . $exception->getMessage(), 0, $exception);
        }
    }
}
