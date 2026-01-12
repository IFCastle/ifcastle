<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Value\ValueBool;
use PHPUnit\Framework\TestCase;

class TypeBoolTest extends TestCase
{
    public function testDecode(): void
    {
        $typeBool                   = new TypeBool('bool');

        $this->assertTrue($typeBool->decode(true));
        $this->assertTrue($typeBool->decode('true'));
        $this->assertTrue($typeBool->decode(1));
        $this->assertFalse($typeBool->decode(false));
        $this->assertFalse($typeBool->decode('false'));
        $this->assertFalse($typeBool->decode(0));
    }

    public function testEncode(): void
    {
        $typeBool                   = new TypeBool('bool');

        $this->assertTrue($typeBool->encode(true));
        $this->assertTrue($typeBool->encode(new ValueBool(true)));
        $this->assertFalse($typeBool->encode(false));
        $this->assertFalse($typeBool->encode(new ValueBool(false)));
    }
}
