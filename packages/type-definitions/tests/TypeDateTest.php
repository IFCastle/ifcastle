<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DecodingException;
use IfCastle\TypeDefinitions\Exceptions\EncodingException;
use PHPUnit\Framework\TestCase;

class TypeDateTest extends TestCase
{
    public function testEncode(): void
    {
        $typeDate = new TypeDate('date');

        $date = new \DateTime('2023-10-01');
        $dateImmutable = new \DateTimeImmutable('2023-10-01');

        $this->assertSame('2023-10-01', $typeDate->encode($date));
        $this->assertSame('2023-10-01', $typeDate->encode($dateImmutable));
    }

    public function testEncodeWithException(): void
    {
        $typeDate = new TypeDate('date');
        $this->expectException(EncodingException::class);
        $typeDate->encode('invalid-date');
    }

    public function testDecode(): void
    {
        $typeDate = new TypeDate('date');

        $this->assertInstanceOf(\DateTimeImmutable::class, $typeDate->decode('2023-10-01'));

        $date = new \DateTime('2023-10-01');
        $this->assertInstanceOf(\DateTimeImmutable::class, $typeDate->decode($date));

        $dateImmutable = new \DateTimeImmutable('2023-10-01');
        $this->assertInstanceOf(\DateTimeImmutable::class, $typeDate->decode($dateImmutable));

        $this->expectException(DecodingException::class);
        $typeDate->decode('invalid-date');
    }
}
