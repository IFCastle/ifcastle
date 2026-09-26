<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer\Http;

use IfCastle\Protocol\Http\HttpResponseInterface;
use IfCastle\Protocol\Http\ResponseMutable;
use PHPUnit\Framework\TestCase;
use TrueAsync\HttpRequest;
use TrueAsync\HttpResponse;

class ResponseWriterTest extends TestCase
{
    private LocalHttpServer $server;

    private HttpResponseInterface $response;

    #[\Override]
    protected function setUp(): void
    {
        $this->server               = new LocalHttpServer(function (HttpRequest $request, HttpResponse $response): void {
            try {
                new ResponseWriter()->write($this->response, $response);
            } catch (\RuntimeException) {
                // A caller that swallows the failure must not turn a cut body into a finished one.
            }
        });
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->server->stop();
    }

    public function testStatusReasonHeadersAndStringBody(): void
    {
        $this->response             = new ResponseMutable()
            ->setStatusCode(201)
            ->setReasonPhrase('Made')
            ->setHeader('Content-Type', 'text/plain')
            ->setHeader('X-Multi', ['a', 'b'])
            ->setBody('created');

        $answer                     = $this->server->request('GET', '/');

        $this->assertSame('HTTP/1.1 201 Made', $answer['status']);
        $headers                    = \array_map(\strtolower(...), $answer['headers']);
        $this->assertContains('content-type: text/plain', $headers);
        $this->assertContains('x-multi: a', $headers);
        $this->assertContains('x-multi: b', $headers);
        $this->assertSame('created', $answer['body']);
    }

    public function testStreamBodyIsWrittenInChunksAndClosed(): void
    {
        $stream                     = new ChunkStream(['a', 'b', 'c']);
        $this->response             = new ResponseMutable()->setStatusCode(200)->setBody($stream);

        $answer                     = $this->server->request('GET', '/');

        $this->assertSame('abc', $answer['body']);
        $this->assertTrue($stream->closed);
    }

    /**
     * Headers are on the wire after the first chunk, so a stream that fails later can only fail
     * the response: HTTP/1 withholds the last chunk, and the client sees the body cut.
     */
    public function testStreamFailingMidBodyFailsTheResponse(): void
    {
        $stream                     = new ChunkStream(['a'], new \RuntimeException('the source broke'));
        $this->response             = new ResponseMutable()->setStatusCode(200)->setBody($stream);

        $answer                     = $this->server->raw("GET / HTTP/1.1\r\nHost: 127.0.0.1\r\nConnection: close\r\n\r\n");

        $this->assertStringContainsString("\r\n1\r\na\r\n", $answer);
        $this->assertStringNotContainsString("\r\n0\r\n\r\n", $answer);
        $this->assertTrue($stream->closed);
    }
}
