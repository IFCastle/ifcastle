<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer\Http;

use Async\Coroutine;
use IfCastle\TrueAsyncWebServer\TestHttpClient;
use TrueAsync\HttpServer;
use TrueAsync\HttpServerConfig;

use function Async\await;
use function Async\spawn;

/**
 * A TrueAsync\HttpServer on a free localhost port, running in a coroutine of the test process,
 * and a blocking-looking HTTP/1.1 client for it.
 */
final class LocalHttpServer
{
    public readonly int $port;

    private readonly TestHttpClient $client;

    private readonly HttpServer $server;

    private readonly Coroutine $coroutine;

    /**
     * @param \Closure(\TrueAsync\HttpRequest, \TrueAsync\HttpResponse): void $handler
     */
    public function __construct(\Closure $handler)
    {
        $this->port                 = TestHttpClient::freePort();
        $this->client               = new TestHttpClient($this->port);
        $this->server               = new HttpServer(
            new HttpServerConfig()->addListener('127.0.0.1', $this->port)->setWorkers(1)
        );

        $this->server->addHttpHandler($handler);
        $this->coroutine            = spawn($this->server->start(...));
        $this->client->waitUntilListening();
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array{status: string, headers: list<string>, body: string} status is the status line
     */
    public function request(string $method, string $path, array $headers = [], string $body = ''): array
    {
        return $this->client->request($method, $path, $headers, $body);
    }

    /**
     * Sends $request as written; see TestHttpClient::raw().
     */
    public function raw(string $request): string
    {
        return $this->client->raw($request);
    }

    public function stop(): void
    {
        $this->server->stop();
        await($this->coroutine);
    }
}
