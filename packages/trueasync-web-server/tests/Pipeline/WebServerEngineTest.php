<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer\Pipeline;

use IfCastle\Application\Bootloader\Builder\ConfigInMemory;
use IfCastle\Application\Environment\SystemEnvironmentInterface;
use IfCastle\DI\ConfigInterface;
use IfCastle\TrueAsyncWebServer\RequestHandler;
use IfCastle\TrueAsyncWebServer\WebServerEngine;
use PHPUnit\Framework\TestCase;

class WebServerEngineTest extends TestCase
{
    private HttpPipeline|null $pipeline = null;

    #[\Override]
    protected function tearDown(): void
    {
        $this->pipeline?->dispose();
        $this->pipeline             = null;
    }

    public function testErrorThePlanCannotRenderAnswers500AndIsLogged(): void
    {
        $this->pipeline             = new HttpPipeline();

        $response                   = $this->pipeline->get(RequestPathRecorder::BREAK_RESPONSE_PATH);

        $this->assertSame(500, $response->status);
        $this->assertCount(1, $this->pipeline->logRecords());
        $this->assertStringContainsString(RequestPathRecorder::BREAK_MESSAGE, $this->pipeline->logRecords()[0]);
    }

    /**
     * The writer's error must not reach TrueAsync, which would send its message to the client.
     */
    public function testResponseThatCannotBeWrittenAnswersAFixed500(): void
    {
        $this->pipeline             = new HttpPipeline();

        $response                   = $this->pipeline->get(RequestPathRecorder::BAD_HEADER_PATH);

        $this->assertSame(500, $response->status);
        $this->assertSame(RequestHandler::SERVER_ERROR, $response->body);
        $this->assertCount(1, $this->pipeline->logRecords());
    }

    public function testSecondStartIsRefused(): void
    {
        $this->pipeline             = new HttpPipeline();

        $this->expectException(\LogicException::class);

        $this->pipeline->engine()->start();
    }

    public function testSigtermEndsStart(): void
    {
        $this->pipeline             = new HttpPipeline();

        \posix_kill(\getmypid(), \SIGTERM);
        $this->pipeline->awaitStopped();

        $this->assertFalse($this->pipeline->client->isListening());
    }

    public function testStopBeforeStartMakesStartReturn(): void
    {
        $engine                     = new WebServerEngine($this->createStub(SystemEnvironmentInterface::class));

        $engine->stop();
        $engine->start();

        $this->assertSame('trueasync-web-server/' . PHP_VERSION, $engine->getEngineName());
    }

    #[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
    public function testStartWithoutAHostIsRefused(): void
    {
        $systemEnvironment          = $this->createMock(SystemEnvironmentInterface::class);
        $systemEnvironment->method('resolveDependency')->with(ConfigInterface::class)
                          ->willReturn(new ConfigInMemory([WebServerEngine::CONFIG_SECTION => ['port' => 9095]]));

        $this->expectException(\InvalidArgumentException::class);

        new WebServerEngine($systemEnvironment)->start();
    }

    // Native H1 parser pooling is process-global; start this contract with a fresh parser pool.
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function testConfiguredBodyLimitRejectsOnlyOversizedRequestsAndKeepsServing(): void
    {
        $this->pipeline             = new HttpPipeline(['maxBodySize' => 1024]);
        $json                       = '{"a":2,"b":3}';
        $headers                    = ['Content-Type' => 'application/json'];
        $below                      = $json . \str_repeat(' ', 1023 - \strlen($json));
        $above                      = $json . \str_repeat(' ', 1025 - \strlen($json));

        $accepted                   = $this->pipeline->request('POST', '/pipeline/sum', $headers, $below);
        $this->assertSame(200, $accepted->status);
        $this->assertSame('5', $accepted->body);

        $rejected                   = $this->pipeline->request('POST', '/pipeline/sum', $headers, $above);
        $this->assertSame(413, $rejected->status);
        $this->assertTrue($this->pipeline->client->isListening());
        $this->assertSame(200, $this->pipeline->get('/pipeline/echo/after/0')->status);
        $this->assertSame([], $this->pipeline->logRecords());
    }

    public static function invalidBodySizes(): array
    {
        return [[0], [-1], ['2048'], [null], [true], [1], [17179869185]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidBodySizes')]
    #[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
    public function testInvalidBodyLimitIsRefusedBeforeListening(mixed $maxBodySize): void
    {
        $systemEnvironment          = $this->createMock(SystemEnvironmentInterface::class);
        $systemEnvironment->method('resolveDependency')->with(ConfigInterface::class)
                          ->willReturn(new ConfigInMemory([WebServerEngine::CONFIG_SECTION => [
                              'host' => '127.0.0.1', 'port' => 9095, 'maxBodySize' => $maxBodySize,
                          ]]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('maxBodySize');

        new WebServerEngine($systemEnvironment)->start();
    }
}
