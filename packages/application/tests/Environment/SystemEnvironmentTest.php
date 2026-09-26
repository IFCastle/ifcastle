<?php

declare(strict_types=1);

namespace IfCastle\Application\Environment;

use IfCastle\Application\RequestEnvironment\RequestEnvironment;
use IfCastle\DI\Resolver;
use PHPUnit\Framework\TestCase;

class SystemEnvironmentTest extends TestCase
{
    public function testWithoutCoroutineContextTheRequestIsSharedWithTheSystemEnvironment(): void
    {
        $systemEnvironment          = new SystemEnvironment(new Resolver());
        $publicEnvironment          = new PublicEnvironment(new Resolver(), [], $systemEnvironment);
        $requestEnvironment         = new RequestEnvironment(parentContainer: $publicEnvironment);

        $publicEnvironment->setRequestEnvironment($requestEnvironment);

        $this->assertSame($requestEnvironment, $systemEnvironment->getRequestEnvironment());
        $this->assertSame($requestEnvironment, $publicEnvironment->getRequestEnvironment());
    }
}
