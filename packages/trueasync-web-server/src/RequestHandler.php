<?php

declare(strict_types=1);

namespace IfCastle\TrueAsyncWebServer;

use IfCastle\Application\Environment\SystemEnvironmentInterface;
use IfCastle\Application\RequestEnvironment\RequestEnvironment;
use IfCastle\Application\RequestEnvironment\RequestEnvironmentInterface;
use IfCastle\Application\RequestEnvironment\RequestPlanInterface;
use IfCastle\Protocol\Http\HttpRequestInterface;
use IfCastle\Protocol\Http\HttpResponseInterface;
use IfCastle\Protocol\ResponseFactoryInterface;
use IfCastle\TrueAsyncWebServer\Http\HttpRequestAdapter;
use IfCastle\TrueAsyncWebServer\Http\ResponseWriter;
use Psr\Log\LoggerInterface;
use TrueAsync\HttpRequest;
use TrueAsync\HttpResponse;

/**
 * Runs one TrueAsync request through the application's request plan and sends what it answered.
 *
 * TrueAsync\HttpServer calls it in a coroutine of the request's own Scope, so the request
 * environment set here is the one every coroutine of that request finds.
 */
final readonly class RequestHandler
{
    public const string SERVER_ERROR = 'Internal server error';

    private ResponseFactory $responseFactory;

    private ResponseWriter $responseWriter;

    public function __construct(
        private RequestPlanInterface $requestPlan,
        private SystemEnvironmentInterface $environment,
        private ?LoggerInterface $logger = null,
    ) {
        $this->responseFactory      = new ResponseFactory();
        $this->responseWriter       = new ResponseWriter();
    }

    /**
     * Nothing but a cancellation leaves this method: TrueAsync would send any other exception's
     * message to the client. An error the plan could not render, a plan that defined no HTTP
     * response and a response that could not be written are logged and answered with a fixed 500;
     * the environment is disposed after the response is written, since a stream body may still
     * read from what the environment holds.
     */
    public function __invoke(HttpRequest $request, HttpResponse $response): void
    {
        $requestEnvironment         = null;

        try {
            $requestEnvironment     = $this->createRequestEnvironment($request);
            $this->responseWriter->write($this->answer($requestEnvironment), $response);
        } catch (\Cancellation $cancellation) {
            // The server's own request refusals (TrueAsync\HttpException) are cancellations too.
            throw $cancellation;
        } catch (\Throwable $throwable) {
            $this->log($throwable);
            self::failResponse($response);
        } finally {
            try {
                $requestEnvironment?->dispose();
            } catch (\Throwable $throwable) {
                $this->log($throwable);
            }
        }
    }

    private function createRequestEnvironment(HttpRequest $request): RequestEnvironmentInterface
    {
        $httpRequest                = new HttpRequestAdapter($request);
        $requestEnvironment         = new RequestEnvironment($httpRequest, $this->environment);
        $requestEnvironment->set(HttpRequestInterface::class, $httpRequest);
        $requestEnvironment->set(ResponseFactoryInterface::class, $this->responseFactory);

        return $requestEnvironment;
    }

    private function answer(RequestEnvironmentInterface $requestEnvironment): HttpResponseInterface
    {
        try {
            $this->environment->setRequestEnvironment($requestEnvironment);
            $this->requestPlan->executePlan($requestEnvironment);
        } catch (\Cancellation $cancellation) {
            throw $cancellation;
        } catch (\Throwable $throwable) {
            // The response defined before the error, if any, still goes out (DECISIONS 2026-09-26).
            $this->log($throwable);
        }

        $answer                     = $requestEnvironment->getResponse();

        if ($answer instanceof HttpResponseInterface) {
            return $answer;
        }

        throw new \UnexpectedValueException('The request plan defined no HTTP response');
    }

    /**
     * A failure before the headers went out becomes a fixed 500; after, the response can only fail.
     */
    private static function failResponse(HttpResponse $response): void
    {
        if ($response->isHeadersSent()) {
            $response->abort();
            return;
        }

        $response->resetHeaders()->setStatusCode(500)->setBody(self::SERVER_ERROR);
    }

    private function log(\Throwable $throwable): void
    {
        try {
            $this->logger?->error($throwable->getMessage(), ['exception' => $throwable]);
        } catch (\Throwable) {
            // A logger that fails cannot report itself; the error log is the last place left.
            \error_log((string) $throwable);
        }
    }
}
