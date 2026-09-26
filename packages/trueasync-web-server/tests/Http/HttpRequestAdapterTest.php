<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer\Http;

use IfCastle\Protocol\Exceptions\HttpErrorInterface;
use IfCastle\Protocol\FileContainerInterface;
use PHPUnit\Framework\TestCase;
use TrueAsync\HttpRequest;
use TrueAsync\HttpResponse;

class HttpRequestAdapterTest extends TestCase
{
    private LocalHttpServer $server;

    /**
     * What the handler read through the adapter; the request is valid only inside the handler.
     *
     * @var array<string, mixed>
     */
    private array $seen             = [];

    /**
     * @var \Closure(HttpRequestAdapter): array<string, mixed>
     */
    private \Closure $inspect;

    #[\Override]
    protected function setUp(): void
    {
        $this->server               = new LocalHttpServer(function (HttpRequest $request, HttpResponse $response): void {
            $this->seen             = ($this->inspect)(new HttpRequestAdapter($request));
            $response->setBody('ok');
        });
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->server->stop();
    }

    public function testUriMethodQueryAndHeaders(): void
    {
        $this->inspect              = static fn(HttpRequestAdapter $request): array => [
            'method'                => $request->getMethod(),
            'path'                  => $request->getUri()->getPath(),
            'host'                  => $request->getUri()->getHost(),
            'parameters'            => $request->getRequestParameters(),
            'header'                => $request->getHeader('X-TEST'),
            'has'                   => $request->hasHeader('x-test'),
        ];

        $this->server->request('GET', '/p/q?x=1&y[]=2', ['X-Test' => 'a']);

        $this->assertSame([
            'method'                => 'GET',
            'path'                  => '/p/q',
            'host'                  => '127.0.0.1',
            'parameters'            => ['x' => '1', 'y' => ['2']],
            'header'                => ['a'],
            'has'                   => true,
        ], $this->seen);
    }

    /**
     * RFC 9112 §3.2.2: a server accepts a target in absolute form, and then the target, not Host,
     * names the resource.
     */
    public function testAbsoluteFormTargetIsTheUri(): void
    {
        $this->inspect              = static fn(HttpRequestAdapter $request): array => [
            'host'                  => $request->getUri()->getHost(),
            'path'                  => $request->getUri()->getPath(),
            'query'                 => $request->getUri()->getQuery(),
        ];

        $this->server->raw("GET http://example.com/p/q?x=1 HTTP/1.1\r\nHost: example.com\r\nConnection: close\r\n\r\n");

        $this->assertSame(['host' => 'example.com', 'path' => '/p/q', 'query' => 'x=1'], $this->seen);
    }

    public function testCookiesAreDecodedAndTheFirstOfEqualNamesWins(): void
    {
        $this->inspect              = static fn(HttpRequestAdapter $request): array => $request->getCookies();

        $this->server->request('GET', '/', ['Cookie' => 'sid=abc%20d; x=1; sid=2']);

        $this->assertSame(['sid' => 'abc d', 'x' => '1'], $this->seen);
    }

    public function testUrlEncodedForm(): void
    {
        $this->inspect              = static fn(HttpRequestAdapter $request): array => [
            'get'                   => $request->retrieveRequestForm()?->get,
            'post'                  => $request->retrieveRequestForm()?->post,
        ];

        $this->server->request(
            'POST', '/form?q=1', ['Content-Type' => 'application/x-www-form-urlencoded'], 'a=1&b%5B%5D=2'
        );

        $this->assertSame(['get' => ['q' => '1'], 'post' => ['a' => '1', 'b' => ['2']]], $this->seen);
    }

    /**
     * The server refuses a form past a limit with its own HttpException, which the IFCastle
     * pipeline would answer with 500; the adapter hands it on as an HTTP error of the same status.
     */
    public function testRefusedFormIsAnHttpErrorWithItsStatus(): void
    {
        $this->inspect              = static function (HttpRequestAdapter $request): array {
            try {
                $request->retrieveRequestForm();

                return ['refused' => false];
            } catch (HttpErrorInterface $error) {
                return ['refused' => true, 'status' => $error->getStatusCode()];
            }
        };

        // One level past the default max_input_nesting_level of 64.
        $this->server->request(
            'POST', '/form', ['Content-Type' => 'application/x-www-form-urlencoded'], 'a' . \str_repeat('[x]', 65) . '=1'
        );

        $this->assertSame(['refused' => true, 'status' => 400], $this->seen);
    }

    public function testMultipartFormWithFiles(): void
    {
        $this->inspect              = static function (HttpRequestAdapter $request): array {
            $form                   = $request->retrieveRequestForm();
            $file                   = $request->getUploadedFile('f');
            $photos                 = $form?->files['photos'] ?? [];

            return [
                'size'              => $request->getBodySize(),
                'post'              => $form?->post,
                'file'              => $file === null ? null : [
                    $file->getFileName(), $file->getMimeType(), $file->getFileSize(), $file->getContents(),
                ],
                'photos'            => \array_map(static fn(FileContainerInterface $photo) => $photo->getContents(), $photos),
            ];
        };

        $boundary                   = 'b0undary';
        $part                       = static fn(string $disposition, string $content, string $type = ''): string
            => '--' . $boundary . "\r\nContent-Disposition: form-data; " . $disposition . "\r\n"
               . ($type === '' ? '' : 'Content-Type: ' . $type . "\r\n") . "\r\n" . $content . "\r\n";

        $body                       = $part('name="a"', '1')
                                    . $part('name="f"; filename="t.txt"', 'hello', 'text/plain')
                                    . $part('name="photos[]"; filename="1.jpg"', 'one', 'image/jpeg')
                                    . $part('name="photos[]"; filename="2.jpg"', 'two', 'image/jpeg')
                                    . '--' . $boundary . "--\r\n";

        $this->server->request('POST', '/upload', ['Content-Type' => 'multipart/form-data; boundary=' . $boundary], $body);

        // The server keeps the parts rather than the bytes; the size is still the body sent.
        $this->assertSame([
            'size'                  => \strlen($body),
            'post'                  => ['a' => '1'],
            'file'                  => ['t.txt', 'text/plain', 5, 'hello'],
            'photos'                => ['one', 'two'],
        ], $this->seen);
    }

    public function testJsonBodyIsNotAForm(): void
    {
        $this->inspect              = static fn(HttpRequestAdapter $request): array => [
            'body'                  => $request->getBody(),
            'size'                  => $request->getBodySize(),
            'form'                  => $request->retrieveRequestForm(),
        ];

        $this->server->request('POST', '/json', ['Content-Type' => 'application/json'], '{"a":1}');

        $this->assertSame(['body' => '{"a":1}', 'size' => 7, 'form' => null], $this->seen);
    }
}
