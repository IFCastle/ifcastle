<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer\Pipeline;

use IfCastle\Protocol\HeadersInterface;
use PHPUnit\Framework\TestCase;
use TrueAsync\HttpRequest;

class HttpPipelineTest extends TestCase
{
    private HttpPipeline|null $pipeline = null;

    #[\Override]
    protected function setUp(): void
    {
        $this->pipeline             = new HttpPipeline();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->pipeline?->dispose();
        $this->pipeline             = null;
    }

    public function testRequestReachesServiceAndReturnsJson(): void
    {
        $response                   = $this->pipeline->get('/pipeline/echo/abc/0');

        $this->assertSame([], $this->pipeline->logRecords());
        $this->assertSame(200, $response->status);
        $this->assertSame(['id' => 'abc'], $response->json());
    }

    public function testConcurrentRequestsGetTheirOwnResponses(): void
    {
        // The first request waits longest, so all three are inside the pipeline at once.
        $responses                  = $this->pipeline->getConcurrently(
            '/pipeline/echo/first/30', '/pipeline/echo/second/10', '/pipeline/echo/third/0'
        );

        $this->assertSame(
            [['id' => 'first'], ['id' => 'second'], ['id' => 'third']],
            \array_map(static fn(PipelineResponse $response) => $response->json(), $responses)
        );
    }

    /**
     * The probe runs after the service's delay, when the other requests have set theirs, and
     * once more from a coroutine it starts with Async\spawn().
     */
    public function testConcurrentRequestsFindTheirOwnRequestEnvironment(): void
    {
        $paths                      = ['/pipeline/echo/first/30', '/pipeline/echo/second/10', '/pipeline/echo/third/0'];
        $responses                  = $this->pipeline->getConcurrently(...$paths);

        $this->assertSame(
            \array_map(static fn(string $path) => [$path], $paths),
            \array_map(static fn(PipelineResponse $response) => $response->header(RequestPathRecorder::FOUND_HEADER), $responses)
        );

        $this->assertSame(
            \array_map(static fn(string $path) => [$path], $paths),
            \array_map(static fn(PipelineResponse $response) => $response->header(RequestPathRecorder::CHILD_HEADER), $responses)
        );
    }

    public function testServiceParameterIsInjectedFromTheRequestEnvironment(): void
    {
        $response                   = $this->pipeline->get('/pipeline/injected-path');

        $this->assertSame([], $this->pipeline->logRecords());
        $this->assertSame('/pipeline/injected-path', \json_decode($response->body));
    }

    public function testOmittedNullableParameterTakesItsDefault(): void
    {
        $this->assertSame('20', $this->pipeline->get('/pipeline/limit')->body);
    }

    public function testUnknownRouteAnswers404(): void
    {
        $response                   = $this->pipeline->get('/pipeline/missing');

        $this->assertSame(404, $response->status);
        $this->assertSame(404, $response->json()['status'] ?? null);
        $this->assertSame([], $this->pipeline->logRecords());
    }

    public function testOriginalRequestIsTheServerRequest(): void
    {
        $response                   = $this->pipeline->get('/pipeline/text/x');

        $this->assertSame([HttpRequest::class], $response->header(RequestPathRecorder::ORIGINAL_HEADER));
    }

    public function testInvalidHostAnswers400(): void
    {
        // The server passes these Host values through; the URI they form is invalid.
        foreach (['a:99999', 'a\\b'] as $host) {
            $answer                 = $this->pipeline->client->raw(
                "GET /pipeline/text/x HTTP/1.1\r\nHost: $host\r\nConnection: close\r\n\r\n"
            );

            $this->assertStringStartsWith('HTTP/1.1 400', $answer, $host);
        }

        $this->assertSame([], $this->pipeline->logRecords());
    }

    public function testWrongMethodAnswers405(): void
    {
        $response                   = $this->pipeline->request('POST', '/pipeline/echo/abc/0');

        $this->assertSame(405, $response->status);
        $this->assertSame(['GET'], $response->header('Allow'));
        $this->assertSame([], $this->pipeline->logRecords());
    }

    public function testGetWithoutContentTypeReachesService(): void
    {
        $response                   = $this->pipeline->request('GET', '/pipeline/echo/abc/0');

        $this->assertSame([], $this->pipeline->logRecords());
        $this->assertSame(['id' => 'abc'], $response->json());
    }

    public function testJsonBodyWithMediaTypeParametersIsParsed(): void
    {
        $response                   = $this->pipeline->request(
            'POST', '/pipeline/sum', [HeadersInterface::CONTENT_TYPE => 'application/json; charset=utf-8'], '{"a": 2, "b": 3}'
        );

        $this->assertSame([], $this->pipeline->logRecords());
        $this->assertSame('5', $response->body);
    }

    public function testJsonBodyThatIsNotAnObjectAnswers400(): void
    {
        $response                   = $this->pipeline->request(
            'POST', '/pipeline/sum', [HeadersInterface::CONTENT_TYPE => HeadersInterface::MIME_APPLICATION_JSON], '42'
        );

        $this->assertSame(400, $response->status);
        $this->assertSame([], $this->pipeline->logRecords());
    }

    public function testEmptyBodyHasNoParametersWhateverItsContentType(): void
    {
        $response                   = $this->pipeline->request(
            'GET', '/pipeline/echo/abc/0', [HeadersInterface::CONTENT_TYPE => 'text/plain']
        );

        $this->assertSame([], $this->pipeline->logRecords());
        $this->assertSame(['id' => 'abc'], $response->json());
    }

    public function testJsonResponseHasOneContentTypeValue(): void
    {
        $response                   = $this->pipeline->get('/pipeline/echo/abc/0');

        $this->assertSame(['application/json; charset=utf-8'], $response->header(HeadersInterface::CONTENT_TYPE));
    }

    public function testScalarResultIsSentAsJson(): void
    {
        $response                   = $this->pipeline->get('/pipeline/text/hello');

        $this->assertSame(200, $response->status);
        $this->assertSame('"hello"', $response->body);
    }

    public function testServiceErrorAnswers500AndIsLogged(): void
    {
        $response                   = $this->pipeline->get('/pipeline/fail');

        $this->assertSame(500, $response->status);
        $this->assertCount(1, $this->pipeline->logRecords());
        $this->assertStringContainsString(PipelineService::FAILURE, $this->pipeline->logRecords()[0]);
    }

    public function testClientVisibleErrorWithoutHttpStatusIsLogged(): void
    {
        $response                   = $this->pipeline->get('/pipeline/fail-visibly');

        $this->assertSame(500, $response->status);
        $this->assertCount(1, $this->pipeline->logRecords());
    }
}
