<?php

declare(strict_types=1);

namespace IfCastle\Application\WorkerProtocol;

use IfCastle\DesignPatterns\Immutable\ImmutableTrait;
use IfCastle\Protocol\FileContainerInterface;
use IfCastle\Protocol\RequestContext;
use IfCastle\Protocol\RequestContextInterface;
use IfCastle\ServiceManager\CommandDescriptorInterface;
use IfCastle\ServiceManager\ExecutionContextInterface;
use League\Uri\Contracts\UriInterface;
use League\Uri\Uri;

final class WorkerRequest implements WorkerRequestInterface
{
    use ImmutableTrait;

    private RequestContextInterface $requestContext;

    public function __construct(
        protected CommandDescriptorInterface $commandDescriptor,
        protected ExecutionContextInterface $executionContext
    ) {
        $this->requestContext = new RequestContext();
    }

    #[\Override]
    public function getCommandDescriptor(): CommandDescriptorInterface
    {
        return $this->commandDescriptor;
    }

    #[\Override]
    public function getExecutionContext(): ExecutionContextInterface
    {
        return $this->executionContext;
    }

    #[\Override]
    public function getHeaders(): array
    {
        return $this->executionContext[self::REQUEST_HEADERS] ?? [];
    }

    #[\Override]
    public function hasHeader(string $name): bool
    {
        return isset($this->executionContext[self::REQUEST_HEADERS][$name]);
    }

    #[\Override]
    public function getHeader(string $name): array
    {
        return $this->executionContext[self::REQUEST_HEADERS][$name] ?? [];
    }

    #[\Override]
    public function getHeaderLine(string $name): string
    {
        return \implode(',', $this->executionContext[self::REQUEST_HEADERS][$name] ?? []);
    }

    #[\Override]
    public function getMethod(): string
    {
        return 'CALL';
    }

    #[\Override] public function getUri(): UriInterface
    {
        return Uri::fromComponents([
            'query'                 => $this->commandDescriptor->getServiceNamespace()
                                      . '/' . $this->commandDescriptor->getServiceName()
                                      . '/' . $this->commandDescriptor->getMethodName(),
        ]);
    }

    #[\Override]
    public function getRequestParameters(): array
    {
        return $this->commandDescriptor->getParameters();
    }

    #[\Override]
    public function getRequestParameter(string $name): mixed
    {
        return $this->commandDescriptor->getParameters()[$name] ?? null;
    }

    #[\Override]
    public function requestParameters(string ...$names): array
    {
        $result                     = [];
        $parameters                 = $this->commandDescriptor->getParameters();

        foreach ($names as $name) {
            if (\array_key_exists($name, $parameters)) {
                $result[$name]      = $parameters[$name];
            }
        }

        return $result;
    }

    #[\Override]
    public function requestParametersWithNull(string ...$names): array
    {
        $result                     = [];
        $parameters                 = $this->commandDescriptor->getParameters();

        foreach ($names as $name) {
            $result[$name]          = $parameters[$name] ?? null;
        }

        return $result;
    }

    #[\Override]
    public function isRequestParametersExist(string ...$names): bool
    {
        $parameters                 = $this->commandDescriptor->getParameters();

        foreach ($names as $name) {
            if (!\array_key_exists($name, $parameters)) {
                return false;
            }
        }

        return true;
    }

    #[\Override]
    public function isRequestParametersDefined(string ...$names): bool
    {
        $parameters                 = $this->commandDescriptor->getParameters();

        foreach ($names as $name) {
            if (!isset($parameters[$name])) {
                return false;
            }
        }

        return true;
    }

    #[\Override]
    public function getUploadedFiles(): array
    {
        return [];
    }

    #[\Override]
    public function getUploadedFile(string $name): ?FileContainerInterface
    {
        return null;
    }

    #[\Override]
    public function hasUploadedFile(string $name): bool
    {
        return false;
    }

    #[\Override]
    public function getRequestContext(): RequestContextInterface
    {
        return $this->requestContext;
    }

    /**
     * @throws \Exception
     */
    #[\Override]
    public function getRequestContextParameters(): array
    {
        return \iterator_to_array($this->requestContext->getIterator());
    }

    #[\Override]
    public function setHeaders(array $headers): static
    {
        $this->throwIfImmutable();

        $this->executionContext[self::REQUEST_HEADERS] = $headers;

        return $this;
    }

    #[\Override]
    public function setHeader(string $header, array|string $value): static
    {
        $this->throwIfImmutable();

        $this->executionContext[self::REQUEST_HEADERS][$header] = (array) $value;

        return $this;
    }

    #[\Override]
    public function resetHeaders(): static
    {
        $this->throwIfImmutable();

        $this->executionContext[self::REQUEST_HEADERS] = [];

        return $this;
    }
}
