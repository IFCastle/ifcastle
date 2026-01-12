<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Http;

use IfCastle\Async\ReadableStreamInterface;
use IfCastle\Protocol\HeadersMutableTrait;

class ResponseMutable implements HttpResponseMutableInterface
{
    use HeadersMutableTrait;

    protected string                $protocolName;

    protected string                $protocolVersion;

    protected string                $protocolRole;

    protected int                   $statusCode;

    protected string                $reasonPhrase;

    protected string|ReadableStreamInterface $body;

    public function __construct(?string $protocolName = null, ?string $protocolVersion = null, ?string $protocolRole = null)
    {
        $this->protocolName         = $protocolName     ?? 'HTTP';
        $this->protocolVersion      = $protocolVersion  ?? '1.1';
        $this->protocolRole         = $protocolRole     ?? 'server';
    }

    #[\Override]
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    #[\Override]
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    #[\Override]
    public function getBody(): string|ReadableStreamInterface
    {
        return $this->body;
    }

    #[\Override]
    public function setStatusCode(int $code): static
    {
        $this->statusCode           = $code;
        return $this;
    }

    #[\Override]
    public function setReasonPhrase(string $reason): static
    {
        $this->reasonPhrase         = $reason;
        return $this;
    }

    #[\Override]
    public function setBody(string|ReadableStreamInterface $body): static
    {
        $this->body                 = $body;
        return $this;
    }

    #[\Override]
    public function getProtocolName(): string
    {
        return $this->protocolName;
    }

    #[\Override]
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    #[\Override]
    public function getProtocolRole(): string
    {
        return $this->protocolRole;
    }
}
