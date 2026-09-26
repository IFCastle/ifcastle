<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer;

use Async\Signal;
use IfCastle\Application\Environment\PublicEnvironmentInterface;
use IfCastle\Application\Environment\SystemEnvironmentInterface;
use IfCastle\Application\RequestEnvironment\RequestPlanInterface;
use IfCastle\DI\ConfigInterface;
use IfCastle\TrueAsync\TrueAsyncEngine;
use Psr\Log\LoggerInterface;
use TrueAsync\HttpServer;
use TrueAsync\HttpServerConfig;

use function Async\await_any_or_fail;
use function Async\signal;
use function Async\spawn;

/**
 * Serves the application's request plan through TrueAsync\HttpServer.
 *
 * Reads the [server] section: host (required) and port (default 9095). One worker thread: a
 * second thread would boot the application again. start() blocks until stop(), SIGTERM or SIGINT.
 */
class WebServerEngine extends TrueAsyncEngine
{
    public const string CONFIG_SECTION = 'server';

    public const int DEFAULT_PORT   = 9095;

    private ?HttpServer $httpServer = null;

    private bool $isStopRequested   = false;

    public function __construct(private readonly SystemEnvironmentInterface $systemEnvironment) {}

    /**
     * Returns at once when stop() came first.
     *
     * @throws \InvalidArgumentException when [server] names no host
     * @throws \LogicException when the engine is already serving
     */
    #[\Override]
    public function start(): void
    {
        if ($this->httpServer !== null) {
            throw new \LogicException('The web server engine is already serving');
        }

        if ($this->isStopRequested) {
            return;
        }

        $config                     = $this->systemEnvironment->resolveDependency(ConfigInterface::class)
                                                              ->requireSection(self::CONFIG_SECTION);

        $host                       = (string) ($config['host'] ?? '');

        if ($host === '') {
            throw new \InvalidArgumentException('The [' . self::CONFIG_SECTION . '] section names no host');
        }

        $httpServer                 = new HttpServer(
            new HttpServerConfig()->addListener($host, (int) ($config['port'] ?? self::DEFAULT_PORT))->setWorkers(1)
        );

        $httpServer->addHttpHandler(new RequestHandler(
            $this->systemEnvironment->resolveDependency(RequestPlanInterface::class),
            $this->systemEnvironment->findDependency(PublicEnvironmentInterface::class) ?? $this->systemEnvironment,
            $this->systemEnvironment->findDependency(LoggerInterface::class),
        ));

        $this->httpServer           = $httpServer;
        $signals                    = spawn(static function () use ($httpServer): void {
            await_any_or_fail([signal(Signal::SIGTERM), signal(Signal::SIGINT)]);
            $httpServer->stop();
        });

        try {
            $httpServer->start();
        } finally {
            $signals->cancel();
            $this->httpServer       = null;
        }
    }

    /**
     * Stops the server gracefully, start() then returns; called before start(), it makes start()
     * return at once. The engine does not start again afterwards.
     */
    public function stop(): void
    {
        $this->isStopRequested      = true;
        $this->httpServer?->stop();
    }

    #[\Override]
    public function getEngineName(): string
    {
        return 'trueasync-web-server/' . PHP_VERSION;
    }
}
