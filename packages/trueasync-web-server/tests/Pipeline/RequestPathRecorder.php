<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer\Pipeline;

use IfCastle\Application\RequestEnvironment\RequestEnvironmentInterface;
use IfCastle\Protocol\Http\HttpRequestInterface;
use IfCastle\Protocol\Http\HttpResponseMutableInterface;

use function Async\await;
use function Async\spawn;

/**
 * Stores the request path in the request environment before the service runs, and after the
 * response copies the path of the environment that SystemEnvironment::getRequestEnvironment()
 * returns into the FOUND_HEADER response header, and into CHILD_HEADER as a coroutine started
 * with Async\spawn() finds it. The paths differ when requests share one environment slot, and
 * the child header is empty when the environment does not reach the request's coroutines.
 * ORIGINAL_HEADER carries the type of RequestEnvironment::originalRequest().
 */
final class RequestPathRecorder
{
    public const string KEY         = 'requestPath';

    public const string FOUND_HEADER = 'X-Found-Request-Path';

    public const string CHILD_HEADER = 'X-Child-Request-Path';

    public const string ORIGINAL_HEADER = 'X-Original-Request';

    /**
     * A request to this path fails at the RESPONSE stage before any response exists, where the
     * request plan does not render errors, so the error reaches the engine.
     */
    public const string BREAK_RESPONSE_PATH = '/pipeline/text/break-response';

    public const string BREAK_MESSAGE = 'RequestPathRecorder broke the response on purpose';

    /**
     * A request to this path gets a response header whose name is not an HTTP token, which
     * TrueAsync\HttpResponse refuses when the response is written.
     */
    public const string BAD_HEADER_PATH = '/pipeline/text/bad-header';

    public function record(RequestEnvironmentInterface $requestEnvironment): void
    {
        $httpRequest                = $requestEnvironment->resolveDependency(HttpRequestInterface::class);

        if ($httpRequest instanceof HttpRequestInterface) {
            $requestEnvironment->set(self::KEY, $httpRequest->getUri()->getPath());
        }
    }

    public function breakResponse(RequestEnvironmentInterface $requestEnvironment): void
    {
        if ($requestEnvironment->findDependency(self::KEY) === self::BREAK_RESPONSE_PATH) {
            throw new \RuntimeException(self::BREAK_MESSAGE);
        }
    }

    public function probe(RequestEnvironmentInterface $requestEnvironment): void
    {
        $systemEnvironment          = $requestEnvironment->getSystemEnvironment();
        $found                      = $systemEnvironment->getRequestEnvironment();
        $foundByChild               = await(spawn(static fn() => $systemEnvironment->getRequestEnvironment()));
        $response                   = $requestEnvironment->getResponse();

        if ($response instanceof HttpResponseMutableInterface) {
            $response->setHeader(self::FOUND_HEADER, (string) $found?->findDependency(self::KEY));
            $response->setHeader(self::CHILD_HEADER, (string) $foundByChild?->findDependency(self::KEY));
            $response->setHeader(self::ORIGINAL_HEADER, \get_debug_type($requestEnvironment->originalRequest()));

            if ($requestEnvironment->findDependency(self::KEY) === self::BAD_HEADER_PATH) {
                $response->setHeader('Bad Header', 'x');
            }
        }
    }
}
