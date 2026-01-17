<?php

declare(strict_types=1);

namespace IfCastle\ServiceManager;

use IfCastle\DesignPatterns\Interceptor\InterceptorInterface;
use IfCastle\DesignPatterns\Interceptor\InterceptorPipeline;
use IfCastle\DesignPatterns\Interceptor\InterceptorRegistryInterface;
use IfCastle\DI\AutoResolverInterface;
use IfCastle\DI\ContainerInterface;
use IfCastle\ServiceManager\Exceptions\ServiceException;
use IfCastle\TypeDefinitions\DefinitionInterface;
use IfCastle\TypeDefinitions\FromEnv;
use IfCastle\TypeDefinitions\FunctionDescriptorInterface;
use IfCastle\TypeDefinitions\TypeInternal;

abstract class ExecutorAbstract implements ExecutorInterface
{
    /**
     * @throws ServiceException
     */
    public static function throwIfServiceNameInvalid(string $serviceName): void
    {
        if (!\preg_match('/^[A-Z][A-Z\/0-9]+$/i', $serviceName)) {
            throw new ServiceException([
                'template'          => 'Invalid service name {serviceName}. Should be match with pattern /[A-Z][A-Z\/0-9]+/i',
                'serviceName'       => $serviceName,
            ]);
        }
    }

    protected ServiceTracerInterface|null $tracer       = null;

    protected ContainerInterface|null $systemEnvironment = null;

    protected AccessCheckerInterface|null $accessChecker = null;

    protected TaskRunnerInterface|null $taskRunner = null;

    /**
     * @var InterceptorInterface<mixed>[]
     */
    protected array $interceptors = [];

    /**
     * @throws \Throwable
     * @throws ServiceException
     */
    #[\Override]
    public function executeCommand(
        string|CommandDescriptorInterface $service,
        ?string                            $command      = null,
        array                              $parameters   = [],
        ?ExecutionContextInterface         $context      = null
    ): mixed {
        if ($service instanceof CommandDescriptorInterface) {
            $command                = $service->getMethodName();
            $parameters             = $service->getParameters();
            $service                = $service->getServiceName();
        }

        self::throwIfServiceNameInvalid($service);

        [$serviceObject, $serviceDescriptor] = $this->resolveService($service);
        $methodDescriptor           = $serviceDescriptor->getServiceMethod($command);

        $this->accessChecker?->checkAccess($serviceObject, $serviceDescriptor, $methodDescriptor, $service, $command);

        if (($job = $this->taskRunner?->tryRunningAsTask($serviceDescriptor, $methodDescriptor, $service, $command, $parameters)) !== null) {
            return $job;
        }

        $parameters                 = $this->normalizeParameters($parameters, $methodDescriptor);

        return $this->runCommand($serviceObject, $serviceDescriptor, $command, $parameters, $service);
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     * @throws ServiceException
     */
    protected function normalizeParameters(array $parameters, FunctionDescriptorInterface $methodDescriptor): array
    {
        $normalized                 = [];

        foreach ($methodDescriptor->getArguments() as $parameter) {
            $parameterName          = $parameter->getName();
            $isParameterExists      = \array_key_exists($parameterName, $parameters);

            //
            // Apply interceptors if exists
            //
            if ($this->interceptors !== []) {
                $result             = new InterceptorPipeline($this, [$parameter, $methodDescriptor, $parameters], ...$this->interceptors)
                                    ->getResult();

                if ($result !== null) {
                    $normalized[$parameterName] = $result;
                    continue;
                }
            }

            if ($parameter->getResolver() !== null) {
                $normalized[$parameterName] = $this->resolveParameter($parameter);
                continue;
            } elseif ($parameter->findAttribute(FromEnv::class) !== null) {

                $value              = $this->extractParameterFromEnv($parameter);

                if ($value !== null) {
                    $normalized[$parameterName] = $value;
                    continue;
                }
            }

            if (false === $parameter->isDefaultValueAvailable() && false === $isParameterExists) {
                throw new ServiceException([
                    'template'      => 'Parameter "{parameter}" required by {service}->{method}',
                    'parameter'     => $parameterName,
                    'service'       => $methodDescriptor->getClassName(),
                    'method'        => $methodDescriptor->getFunctionName(),
                ]);
            }

            if (false === $isParameterExists) {

                if ($parameter->isDefaultValueAvailable()) {
                    $normalized[$parameterName] = $parameter->getDefaultValue();
                }

                if ($parameter->isNullable()) {
                    $normalized[$parameterName] = null;
                }

                continue;
            }

            if (\is_object($parameters[$parameterName]) || $parameter instanceof TypeInternal) {
                $normalized[$parameterName] = $parameters[$parameterName];
            } else {
                $normalized[$parameterName] = $parameter->decode($parameters[$parameterName]);
            }
        }

        return $normalized;
    }

    protected function resolveParameter(DefinitionInterface $parameter): mixed
    {
        $resolver                   = $parameter->getResolver();

        if ($resolver === null) {
            return null;
        }

        if ($resolver instanceof AutoResolverInterface) {
            $resolver->resolveDependencies($this->systemEnvironment);
        }

        return $resolver($parameter);
    }

    protected function extractParameterFromEnv(DefinitionInterface $parameter): mixed
    {
        $fromEnv            = $parameter->findAttribute(FromEnv::class);
        $env                = $this->systemEnvironment;
        $key                = $fromEnv->key ?? $parameter->getName();

        /* @var FromEnv $fromEnv */

        if ($fromEnv->fromRequestEnv) {
            $env            = $this->getRequestEnv();
        }

        if ($env === null) {
            return null;
        }

        if ($fromEnv->factory !== null) {

            if (false === $env->hasDependency($fromEnv->factory)) {
                return null;
            }

            $env            = $env->findDependency($fromEnv->factory);
        }

        if ($env instanceof ContainerInterface && $env->hasDependency($key)) {
            return $env->resolveDependency($key);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $parameters
     * @throws \Throwable
     */
    protected function runCommand(
        object                     $service,
        ServiceDescriptorInterface $serviceDescriptor,
        string                     $method,
        array                      $parameters,
        string                     $serviceName
    ): mixed {
        $this->tracer?->startServiceCall($serviceName, $serviceDescriptor, $method, $parameters);

        try {

            $result                 = \call_user_func([$service, $method], ...\array_values($parameters));

            $this->tracer?->recordResult($result);

            return $result;

        } catch (\Throwable $throwable) {
            $this->tracer?->recordException($throwable);
            throw $throwable;
        } finally {
            $this->tracer?->end();
        }
    }

    protected function getRequestEnv(): ContainerInterface|null
    {
        return null;
    }

    protected function initializeInterceptors(): void
    {
        $interceptors               = $this->systemEnvironment
                                    ?->findDependency(InterceptorRegistryInterface::class)
                                    ?->resolveInterceptors(ParameterResolverInterface::class);

        if ($interceptors !== null) {
            $this->interceptors      = $interceptors;
        }
    }

    /**
     * @return array{object|null, ServiceDescriptorInterface|null}
     */
    abstract protected function resolveService(string $serviceName): array;
}
