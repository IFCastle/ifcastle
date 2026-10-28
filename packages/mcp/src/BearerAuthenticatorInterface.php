<?php

declare(strict_types=1);

namespace IfCastle\Mcp;

use IfCastle\Protocol\Exceptions\TooManyRequests;
use IfCastle\Protocol\Http\HttpRequestInterface;

/**
 * The host application's credential check, called once per request before the body is read. The server knows no
 * token format or store: it passes the bearer value and runs every tool as the principal this returns.
 */
interface BearerAuthenticatorInterface
{
    /**
     * @param ?string $token the credential of `Authorization: Bearer <token>`; null when the header is absent or
     *     names another scheme, so the implementation can count that failure too
     * @return ?object the principal the request's tools receive; null answers 401 with a Bearer challenge
     * @throws TooManyRequests answers 429
     */
    public function authenticate(?string $token, HttpRequestInterface $request): ?object;
}
