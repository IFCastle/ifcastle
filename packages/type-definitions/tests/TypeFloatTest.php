<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use PHPUnit\Framework\TestCase;

class TypeFloatTest extends TestCase
{
    public function testEncode(): void
    {
        $type                           = new TypeFloat('test');

        $this->assertSame(1.0, $type->encode(1));
        $this->assertSame(1.0, $type->encode(1.0));
        $this->assertSame(1.0, $type->encode('1'));
        $this->assertSame(1.0, $type->encode('1.0'));
        $this->assertSame(1.0, $type->encode('1.00'));

        $this->assertSame(505.544, $type->encode('505.544'));

        // e* notation
        $this->assertSame(5e34, $type->encode('5e34'));
    }

    public function testDecode(): void
    {
        $type                           = new TypeFloat('test');

        $this->assertSame(1.0, $type->decode(1));
        $this->assertSame(1.0, $type->decode(1.0));
        $this->assertSame(1.0, $type->decode('1'));
        $this->assertSame(1.0, $type->decode('1.0'));
        $this->assertSame(1.0, $type->decode('1.00'));

        $this->assertSame(505.544, $type->decode('505.544'));

        // e* notation
        $this->assertSame(5e34, $type->decode('5e34'));
    }
}
