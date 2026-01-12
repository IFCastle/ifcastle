<?php


declare(strict_types=1);

namespace IfCastle\Application\WorkerProtocol;

use IfCastle\Application\Environment\SystemEnvironment;
use IfCastle\DI\Resolver;
use IfCastle\TypeDefinitions\Exceptions\RemoteException;
use IfCastle\TypeDefinitions\Value\ValueBool;
use IfCastle\TypeDefinitions\Value\ValueNumber;
use IfCastle\TypeDefinitions\Value\ValueString;
use PHPUnit\Framework\TestCase;

class WorkerProtocolArrayTypedTest extends TestCase
{
    public function testRequest(): void
    {
        $systemEnvironment          = new SystemEnvironment(new Resolver(), []);
        $workerProtocolArrayTyped   = new WorkerProtocolArrayTyped($systemEnvironment);

        $request                    = $workerProtocolArrayTyped->buildWorkerRequest(
            'service',
            'command',
            [
                'parameter1' => new ValueString('value1'),
                'parameter2' => new ValueNumber(500),
                'parameter3' => new ValueBool(true),
            ],
        );

        $parsed                     = $workerProtocolArrayTyped->parseWorkerRequest($request);
        $parameters                 = $parsed->getCommandDescriptor()->getParameters();

        $this->assertEquals('service', $parsed->getCommandDescriptor()->getServiceName());
        $this->assertEquals('command', $parsed->getCommandDescriptor()->getMethodName());
        $this->assertEquals('value1', $parameters['parameter1'] ?? null);
        $this->assertEquals(500, $parameters['parameter2'] ?? null);
        $this->assertTrue($parameters['parameter3'] ?? null);
    }

    public function testResponse(): void
    {
        $systemEnvironment          = new SystemEnvironment(new Resolver(), []);
        $workerProtocolArrayTyped   = new WorkerProtocolArrayTyped($systemEnvironment);

        $response                   = $workerProtocolArrayTyped->buildWorkerResponse(
            new ValueString('value1'),
        );

        $parsed                     = $workerProtocolArrayTyped->parseWorkerResponse($response);

        $this->assertEquals('value1', $parsed);
    }

    public function testResponseWithException(): void
    {
        $systemEnvironment          = new SystemEnvironment(new Resolver(), []);
        $workerProtocolArrayTyped   = new WorkerProtocolArrayTyped($systemEnvironment);

        $response                   = $workerProtocolArrayTyped->buildWorkerResponse(
            new \Exception('exception'),
        );

        $parsed                     = $workerProtocolArrayTyped->parseWorkerResponse($response);

        $this->assertInstanceOf(RemoteException::class, $parsed);
        $this->assertEquals('exception', $parsed->getMessage());
    }
}
