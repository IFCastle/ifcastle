<?php

declare(strict_types=1);

namespace IfCastle\Protocol\Http;

use PHPUnit\Framework\TestCase;

class ResponseMutableTest extends TestCase
{
    public function testFreshResponseCanBeRead(): void
    {
        $response                   = new ResponseMutable();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $response->getReasonPhrase());
        $this->assertSame('', $response->getBody());
    }

    public function testHeaderLookupIgnoresCase(): void
    {
        $response                   = new ResponseMutable();
        $response->setHeader('Content-Type', ['text/plain', 'charset=utf-8']);

        $this->assertTrue($response->hasHeader('CONTENT-TYPE'));
        $this->assertSame(['text/plain', 'charset=utf-8'], $response->getHeader('content-type'));
        $this->assertSame('text/plain,charset=utf-8', $response->getHeaderLine('Content-type'));
    }

    public function testSetHeaderReplacesUnderTheFirstSpelling(): void
    {
        $response                   = new ResponseMutable();
        $response->setHeader('Content-Type', 'text/plain');
        $response->setHeader('content-type', 'application/json');

        $this->assertSame(['Content-Type' => ['application/json']], $response->getHeaders());
    }

    public function testNewHeaderAcceptsAListOfValues(): void
    {
        $response                   = new ResponseMutable();
        $response->setHeader('Allow', ['GET', 'POST']);

        $this->assertSame(['GET', 'POST'], $response->getHeader('allow'));
    }
}
