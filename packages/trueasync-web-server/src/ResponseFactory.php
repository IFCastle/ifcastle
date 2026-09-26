<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer;

use IfCastle\Protocol\Http\ResponseMutable;
use IfCastle\Protocol\ResponseFactoryInterface;
use IfCastle\Protocol\ResponseInterface;

/**
 * Creates the mutable HTTP response a request plan fills in; it holds no state, so one instance serves
 * every request.
 */
final class ResponseFactory implements ResponseFactoryInterface
{
    #[\Override]
    public function createResponse(
        ?string $protocolName       = null,
        ?string $protocolVersion    = null,
        ?string $protocolRole       = null,
    ): ResponseInterface {
        return new ResponseMutable($protocolName, $protocolVersion, $protocolRole);
    }
}
