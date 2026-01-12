<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DecodingException;
use IfCastle\TypeDefinitions\Exceptions\EncodingException;
use PHPUnit\Framework\TestCase;

class TypeDateTimeTest extends TestCase
{
    public function testValidateValueWithDateTime(): void
    {
        $typeDateTime = new TypeDateTime('test');
        $this->assertTrue($typeDateTime->validate(new \DateTime(), false) === null);
        $this->assertTrue($typeDateTime->validate(new \DateTimeImmutable(), false) === null);
    }

    public function testValidateValueWithInvalidValue(): void
    {
        $typeDateTime = new TypeDateTime('test');
        $this->assertFalse($typeDateTime->validate('invalid-date', false) === null);
    }

    public function testEncodeWithDateTime(): void
    {
        $typeDateTime = new TypeDateTime('test');
        $dateTime = new \DateTime('2023-10-01 12:00:00');
        $this->assertEquals('2023-10-01 12:00:00', $typeDateTime->encode($dateTime));
    }

    public function testEncodeWithInvalidValue(): void
    {
        $this->expectException(EncodingException::class);
        $typeDateTime = new TypeDateTime('test');
        $typeDateTime->encode('invalid-date');
    }

    public function testDecodeWithValidString(): void
    {
        $typeDateTime = new TypeDateTime('test');
        $dateTime = $typeDateTime->decode('2023-10-01 12:00:00');
        $this->assertInstanceOf(\DateTimeImmutable::class, $dateTime);
        $this->assertEquals('2023-10-01 12:00:00', $dateTime->format('Y-m-d H:i:s'));
    }

    public function testDecodeWithInvalidValue(): void
    {
        $this->expectException(DecodingException::class);
        $typeDateTime = new TypeDateTime('test');
        $typeDateTime->decode('invalid-date');
    }
}
