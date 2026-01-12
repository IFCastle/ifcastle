<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DecodingException;
use IfCastle\TypeDefinitions\Exceptions\EncodingException;
use IfCastle\TypeDefinitions\Value\ValueTime;
use PHPUnit\Framework\TestCase;

class TypeTimeTest extends TestCase
{
    public function testValidateValueWithValidTime(): void
    {
        $typeTime = new TypeTime('test');
        $this->assertTrue($typeTime->validate('12:34', false) === null);
        $this->assertTrue($typeTime->validate('12:34:56', false) === null);
        $this->assertTrue($typeTime->validate('12:34:56.789', false) === null, 'Microseconds are optional');
        $this->assertTrue($typeTime->validate('12:34:56.789123', false) === null, 'Microseconds are optional');
        $this->assertTrue($typeTime->validate('12:34:56.789123+02:00', false) === null, 'Timezone is optional');
    }

    public function testValidateValueWithInvalidTime(): void
    {
        $typeTime = new TypeTime('test');
        $this->assertFalse($typeTime->validate('invalid-time', false) === null);
        $this->assertFalse($typeTime->validate('25:00', false) === null);
        $this->assertFalse($typeTime->validate('12:60', false) === null);
        $this->assertFalse($typeTime->validate('12:34:60', false) === null);
        $this->assertFalse($typeTime->validate('12:34:56.1000000', false) === null);
    }

    public function testEncodeWithValueTime(): void
    {
        $typeTime = new TypeTime('test');
        $valueTime = new ValueTime(12, 34, 56, 789123, 7200);
        $this->assertEquals('12:34:56.789123+02:00', $typeTime->encode($valueTime));
    }

    public function testEncodeWithInvalidValue(): void
    {
        $this->expectException(EncodingException::class);
        $typeTime = new TypeTime('test');
        $typeTime->encode('invalid-time');
    }

    public function testDecodeWithValidString(): void
    {
        $typeTime = new TypeTime('test');
        $valueTime = $typeTime->decode('12:34:56.789123+02:00');
        $this->assertInstanceOf(ValueTime::class, $valueTime);
        $this->assertEquals(12, $valueTime->getHour());
        $this->assertEquals(34, $valueTime->getMinute());
        $this->assertEquals(56, $valueTime->getSecond());
        $this->assertEquals(789123, $valueTime->getMicrosecond());
        $this->assertEquals(7200, $valueTime->getTimezoneOffset());
    }

    public function testDecodeWithInvalidString(): void
    {
        $this->expectException(DecodingException::class);
        $typeTime = new TypeTime('test');
        $typeTime->decode('invalid-time');
    }
}
