<?php

declare(strict_types=1);

namespace IfCastle\Mcp;

use IfCastle\Async\ReadableStreamInterface;
use IfCastle\Protocol\FileContainerInterface;
use IfCastle\Protocol\HeadersTrait;
use IfCastle\Protocol\Http\HttpRequestForm;
use IfCastle\Protocol\Http\HttpRequestInterface;
use Psr\Http\Message\UriInterface;

/** An HTTP request with a method, headers and a body, and nothing else McpServer reads. */
final class FakeRequest implements HttpRequestInterface
{
    use HeadersTrait;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(private readonly string $method, array $headers = [], private readonly string $body = '')
    {
        $this->headers          = \array_map(static fn(string $value): array => [$value], $headers);
    }

    public function getUri(): UriInterface
    {
        throw new \LogicException('McpServer does not read the URI');
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getCookies(): array
    {
        return [];
    }

    public function getBodySize(): int
    {
        return \strlen($this->body);
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getBodyStream(): ?ReadableStreamInterface
    {
        return null;
    }

    public function retrieveRequestForm(): HttpRequestForm|null
    {
        return null;
    }

    public function getRequestParameters(): array
    {
        return [];
    }

    public function getRequestParameter(string $name): mixed
    {
        return null;
    }

    public function requestParameters(string ...$names): array
    {
        return [];
    }

    public function requestParametersWithNull(string ...$names): array
    {
        return [];
    }

    public function isRequestParametersExist(string ...$names): bool
    {
        return false;
    }

    public function isRequestParametersDefined(string ...$names): bool
    {
        return false;
    }

    public function getUploadedFiles(): array
    {
        return [];
    }

    public function getUploadedFile(string $name): ?FileContainerInterface
    {
        return null;
    }

    public function hasUploadedFile(string $name): bool
    {
        return false;
    }
}
