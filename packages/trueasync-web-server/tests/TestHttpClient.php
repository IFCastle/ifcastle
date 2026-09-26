<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer;

use PHPUnit\Framework\Assert;

use function Async\delay;

/**
 * A blocking-looking HTTP/1.1 client for a server on 127.0.0.1 in the test process; each call
 * suspends only the calling coroutine, so the server keeps running beside it.
 */
final readonly class TestHttpClient
{
    private const int START_LIMIT_MS = 2000;

    public function __construct(public int $port) {}

    public static function freePort(): int
    {
        $probe                      = \stream_socket_server('tcp://127.0.0.1:0');
        Assert::assertIsResource($probe, 'No free local port');

        $address                    = (string) \stream_socket_get_name($probe, false);
        \fclose($probe);

        return (int) \substr($address, (int) \strrpos($address, ':') + 1);
    }

    /**
     * @param \Closure(): void|null $whileWaiting called on every poll, to fail early when the
     *                                            server is known not to come up
     */
    public function waitUntilListening(?\Closure $whileWaiting = null): void
    {
        for ($waited = 0; $waited < self::START_LIMIT_MS; $waited += 5) {
            if ($whileWaiting !== null) {
                $whileWaiting();
            }

            $connection             = @\stream_socket_client('tcp://127.0.0.1:' . $this->port, timeout: 1);

            if (\is_resource($connection)) {
                \fclose($connection);
                return;
            }

            delay(5);
        }

        Assert::fail('The server did not start listening within ' . self::START_LIMIT_MS . ' ms');
    }

    public function isListening(): bool
    {
        $connection                 = @\stream_socket_client('tcp://127.0.0.1:' . $this->port, timeout: 1);

        if (\is_resource($connection)) {
            \fclose($connection);
            return true;
        }

        return false;
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
}
