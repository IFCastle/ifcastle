<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Http;

use IfCastle\Async\ReadableStreamInterface;
use IfCastle\Protocol\HeadersMutableInterface;

interface HttpResponseMutableInterface extends HttpResponseInterface, HeadersMutableInterface
{
    public function setStatusCode(int $code): static;

    public function setReasonPhrase(string $reason): static;

    public function setBody(string|ReadableStreamInterface $body): static;
}
