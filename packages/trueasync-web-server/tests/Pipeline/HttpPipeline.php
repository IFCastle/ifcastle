<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer\Pipeline;

use Async\Coroutine;
use IfCastle\Application\ApplicationInterface;
use IfCastle\Application\Bootloader\Builder\BootloaderBuilderInMemory;
use IfCastle\Application\EngineInterface;
use IfCastle\Application\Environment\SystemEnvironmentInterface;
use IfCastle\Application\Runner;
use IfCastle\RestApi\Bootloader as RestApiBootloader;
use IfCastle\TrueAsync\Bootloader as TrueAsyncBootloader;
use IfCastle\TrueAsyncWebServer\Bootloader as WebServerBootloader;
use IfCastle\TrueAsyncWebServer\TestHttpClient;
use IfCastle\TrueAsyncWebServer\WebServerEngine;
use PHPUnit\Framework\Assert;
use Psr\Log\LoggerInterface;

use function Async\await;
use function Async\spawn;

/**
 * A booted IFCastle application with rest-api, served by WebServerEngine on a free localhost port.
 *
 * The application runs in a coroutine of the test process and requests reach it over HTTP, so
 * each one goes through TrueAsync\HttpServer and its own request Scope, as in production. The
 * application directory is a temporary directory removed by dispose().
 */
final class HttpPipeline
{
    public readonly TestHttpClient $client;

    private ?Runner $runner         = null;

    private ?Coroutine $application = null;

    private string $appDir;

    private SystemEnvironmentInterface $systemEnvironment;

    public function __construct()
    {
        $this->appDir               = \sys_get_temp_dir() . '/ifcastle-pipeline-' . \bin2hex(\random_bytes(6));
        // ApplicationAbstract refuses to start without a vendor directory.
        \mkdir($this->appDir . '/vendor', 0o777, true);

        $this->client               = new TestHttpClient(TestHttpClient::freePort());
        $config                     = ['server' => ['host' => '127.0.0.1', 'port' => $this->client->port]];

        $this->runner               = new PipelineRunner($this->appDir, 'test', PipelineApplication::class)
            ->defineBootloaderBuilder(new BootloaderBuilderInMemory($this->appDir, 'test', [], [
                TrueAsyncBootloader::class,
                // Before rest-api: its RESPONSE handler then runs ahead of the response strategy.
                PipelineBootloader::class,
                RestApiBootloader::class,
                WebServerBootloader::class,
            ], $config));

        $application                = spawn($this->runner->run(...));
        $this->application          = $application;
        $this->client->waitUntilListening(function () use ($application): void {
            if ($application->isCompleted()) {
                $this->failOnCriticalLog();
            }
        });

        $application                = $this->runner->application();
        Assert::assertInstanceOf(ApplicationInterface::class, $application);
        $this->systemEnvironment    = $application->getSystemEnvironment();
    }

    /**
     * @return list<string> records written to the application logger, "level: message"
     */
    public function logRecords(): array
    {
        $logger                     = $this->systemEnvironment->resolveDependency(LoggerInterface::class);
        Assert::assertInstanceOf(CollectingLogger::class, $logger);

        return $logger->records;
    }

    public function engine(): WebServerEngine
    {
        $engine                     = $this->systemEnvironment->resolveDependency(EngineInterface::class);
        Assert::assertInstanceOf(WebServerEngine::class, $engine);

        return $engine;
    }

    /**
     * @param array<string, string> $headers
     */
    public function request(string $method, string $path, array $headers = [], string $body = ''): PipelineResponse
    {
        return PipelineResponse::fromClient($this->client->request($method, $path, $headers, $body));
    }

    public function get(string $path): PipelineResponse
    {
        return $this->request('GET', $path);
    }

    /**
     * Sends every GET in a coroutine of its own and returns the responses in request order.
     *
     * @return list<PipelineResponse>
     */
    public function getConcurrently(string ...$paths): array
    {
        $coroutines                 = \array_map(fn(string $path) => spawn($this->get(...), $path), $paths);

        return \array_map(static fn(Coroutine $coroutine) => await($coroutine), $coroutines);
    }

    /**
     * Waits until the application's run() returns, after the engine stopped however it stopped.
     */
    public function awaitStopped(): void
    {
        if ($this->application !== null) {
            await($this->application);
            $this->application      = null;
        }
    }

    public function dispose(): void
    {
        if ($this->application !== null) {
            $this->engine()->stop();
            $this->awaitStopped();
        }

        $this->runner?->dispose();
        $this->runner               = null;

        foreach (['/vendor', '/logs/critical.log', '/logs'] as $path) {
            $path                   = $this->appDir . $path;

            if (\is_dir($path)) {
                \rmdir($path);
            } elseif (\is_file($path)) {
                \unlink($path);
            }
        }

        \rmdir($this->appDir);
    }

    /**
     * Runner does not rethrow a failure after the application object exists; it writes the
     * failure to logs/critical.log instead.
     */
    private function failOnCriticalLog(): void
    {
        $criticalLog                = $this->appDir . '/logs/critical.log';

        if (\is_file($criticalLog)) {
            Assert::fail('The application failed to boot: ' . \file_get_contents($criticalLog));
        }
    }
}
