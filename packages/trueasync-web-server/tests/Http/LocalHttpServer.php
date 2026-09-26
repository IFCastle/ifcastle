<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer\Http;

use Async\Coroutine;
use PHPUnit\Framework\Assert;
use TrueAsync\HttpServer;
use TrueAsync\HttpServerConfig;

use function Async\await;
use function Async\delay;
use function Async\spawn;

/**
 * A TrueAsync\HttpServer on a free localhost port, running in a coroutine of the test process,
 * and a blocking-looking HTTP/1.1 client for it.
 */
final class LocalHttpServer
{
    private const int START_LIMIT_MS = 2000;

    public readonly int $port;

    private readonly HttpServer $server;

    private readonly Coroutine $coroutine;

    /**
     * @param \Closure(\TrueAsync\HttpRequest, \TrueAsync\HttpResponse): void $handler
     */
    public function __construct(\Closure $handler)
    {
        $this->port                 = self::freePort();
        $this->server               = new HttpServer(
            new HttpServerConfig()->addListener('127.0.0.1', $this->port)->setWorkers(1)
        );

        $this->server->addHttpHandler($handler);
        $this->coroutine            = spawn($this->server->start(...));
        $this->waitUntilListening();
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array{status: string, headers: list<string>, body: string} status is the status line
     */
    public function request(string $method, string $path, array $headers = [], string $body = ''): array
    {
        $headerLines                = '';

        foreach ($headers as $name => $value) {
            $headerLines           .= $name . ': ' . $value . "\r\n";
        }

        $context                    = \stream_context_create(['http' => [
            'method'                => $method,
            'header'                => $headerLines,
            'content'               => $body,
            'ignore_errors'         => true,
        ]]);

        $responseBody               = \file_get_contents('http://127.0.0.1:' . $this->port . $path, false, $context);
        $responseHeaders            = \http_get_last_response_headers() ?? [];

        Assert::assertIsString($responseBody, 'The server did not answer ' . $method . ' ' . $path);

        return [
            'status'                => \array_shift($responseHeaders) ?? '',
            'headers'               => $responseHeaders,
            'body'                  => $responseBody,
        ];
    }

    /**
     * Sends $request as written, for request lines the HTTP client cannot produce, and returns
     * the raw response. $request must ask the server to close the connection.
     */
    public function raw(string $request): string
    {
        $socket                     = \stream_socket_client('tcp://127.0.0.1:' . $this->port, $code, $message, 2);
        Assert::assertIsResource($socket, 'No connection: ' . $message);

        \fwrite($socket, $request);
        $response                   = (string) \stream_get_contents($socket);
        \fclose($socket);

        return $response;
    }

    public function stop(): void
    {
        $this->server->stop();
        await($this->coroutine);
    }

    private static function freePort(): int
    {
        $probe                      = \stream_socket_server('tcp://127.0.0.1:0');
        Assert::assertIsResource($probe, 'No free local port');

        $address                    = (string) \stream_socket_get_name($probe, false);
        \fclose($probe);

        return (int) \substr($address, (int) \strrpos($address, ':') + 1);
    }

    private function waitUntilListening(): void
    {
        for ($waited = 0; $waited < self::START_LIMIT_MS; $waited += 5) {
            $connection             = @\stream_socket_client('tcp://127.0.0.1:' . $this->port, timeout: 1);

            if (\is_resource($connection)) {
                \fclose($connection);
                return;
            }

            delay(5);
        }

        Assert::fail('The server did not start listening within ' . self::START_LIMIT_MS . ' ms');
    }
}
