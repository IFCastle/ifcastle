<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Http;

use IfCastle\Async\ReadableStreamInterface;
use IfCastle\Protocol\HeadersInterface;
use IfCastle\Protocol\RequestParametersInterface;
use Psr\Http\Message\UriInterface as PsrUri;

interface HttpRequestInterface extends HeadersInterface, RequestParametersInterface
{
    public function getUri(): PsrUri;

    public function getMethod(): string;

    /**
     * @return array<string, string>
     */
    public function getCookies(): array;

    public function getBodySize(): int;

    public function getBody(): string;

    public function getBodyStream(): ?ReadableStreamInterface;

    /**
     * The method retrieves the HTTP FORM from the request if possible.
     * That is, the method checks the Content Type,
     * and if it matches one of the specified options: application/x-www-form-urlencoded or form-data,
     * the method returns a form object.
     *
     */
    public function retrieveRequestForm(): HttpRequestForm|null;
}
