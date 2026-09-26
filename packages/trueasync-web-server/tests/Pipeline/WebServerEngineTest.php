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
}
