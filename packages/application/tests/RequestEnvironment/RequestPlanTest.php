<?php

declare(strict_types=1);

namespace IfCastle\Application\RequestEnvironment;

use IfCastle\Protocol\Exceptions\BadRequest;
use IfCastle\Protocol\Exceptions\ParseException;
use IfCastle\TypeDefinitions\ResultInterface;
use PHPUnit\Framework\TestCase;

class RequestPlanTest extends TestCase
{
    /**
     * @var list<string>
     */
    private array $calls            = [];

    private RequestPlan $plan;

    private RequestEnvironment $environment;

    #[\Override]
    protected function setUp(): void
    {
        $this->calls                = [];
        $this->plan                 = new RequestPlan();
        $this->environment          = new RequestEnvironment();
        $this->plan->addFinallyHandler($this->record('finally'));
    }

    public function testErrorBeforeResponseIsLeftForTheResponseStage(): void
    {
        $failure                    = new \RuntimeException('build failed');

        $this->plan->addBuildHandler($this->throwing($failure))
                   ->addExecuteHandler($this->record('execute'))
                   ->addResponseHandler($this->record('response'))
                   ->addAfterResponseHandler($this->record('after-response'));

        $this->plan->executePlan($this->environment);

        $this->assertSame(['response', 'after-response', 'finally'], $this->calls);
        $this->assertSame($failure, $this->resultError());
    }

    public function testParseErrorBeforeResponseBecomesBadRequest(): void
    {
        $this->plan->addBuildHandler($this->throwing(new ParseException('broken body')))
                   ->addResponseHandler($this->record('response'));

        $this->plan->executePlan($this->environment);

        $error                      = $this->resultError();
        $this->assertInstanceOf(BadRequest::class, $error);
        $this->assertSame('broken body', $error->getDetail());
    }

    public function testErrorIsThrownWhenNoHandlerRendersIt(): void
    {
        $failure                    = new \RuntimeException('build failed');
        $this->plan->addBuildHandler($this->throwing($failure));

        try {
            $this->plan->executePlan($this->environment);
            $this->fail('The error was swallowed');
        } catch (\RuntimeException $exception) {
            $this->assertSame($failure, $exception);
        }

        $this->assertSame(['finally'], $this->calls);
    }

    public function testErrorAfterResponseIsThrownAfterFinallyAndKeepsTheEarlierError(): void
    {
        $serviceFailure             = new \RuntimeException('service failed');
        $lateFailure                = new \RuntimeException('after-response failed');

        $this->plan->addBuildHandler($this->throwing($serviceFailure))
                   ->addResponseHandler($this->record('response'))
                   ->addAfterResponseHandler($this->throwing($lateFailure));

        try {
            $this->plan->executePlan($this->environment);
            $this->fail('The late error was swallowed');
        } catch (\RuntimeException $exception) {
            $this->assertSame($lateFailure, $exception);
        }

        $this->assertSame(['response', 'finally'], $this->calls);
        $this->assertSame($serviceFailure, $this->resultError());
    }

    private function record(string $name): \Closure
    {
        return function () use ($name): void {
            $this->calls[]          = $name;
        };
    }

    private function throwing(\Throwable $error): \Closure
    {
        return static function () use ($error): never {
            throw $error;
        };
    }

    private function resultError(): ?\Throwable
    {
        $result                     = $this->environment->findDependency(ResultInterface::class);
        $this->assertInstanceOf(ResultInterface::class, $result);

        return $result->getError();
    }
}
