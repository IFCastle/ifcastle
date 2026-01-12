<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use PHPUnit\Framework\TestCase;

class TypeStringTest extends TestCase
{
    public function testEncode(): void
    {
        $typeString                 = new TypeString('string');

        $this->assertSame('1', $typeString->encode(true));
        $this->assertSame('true', $typeString->encode('true'));
        $this->assertSame('1', $typeString->encode(1));
        $this->assertSame('', $typeString->encode(false));
        $this->assertSame('false', $typeString->encode('false'));
        $this->assertSame('0', $typeString->encode(0));
    }

    public function testDecode(): void
    {
        $typeString                 = new TypeString('string');

        $this->assertSame('1', $typeString->decode(true));
        $this->assertSame('true', $typeString->decode('true'));
        $this->assertSame('1', $typeString->decode(1));
        $this->assertSame('', $typeString->decode(false));
        $this->assertSame('false', $typeString->decode('false'));
        $this->assertSame('0', $typeString->decode(0));
    }
}
