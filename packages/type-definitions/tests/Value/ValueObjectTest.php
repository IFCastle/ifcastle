<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Value;

use PHPUnit\Framework\TestCase;

class ValueObjectTest extends TestCase
{
    public function testJsonDecodeOfInvalidJsonThrowsJsonException(): void
    {
        $this->expectException(\JsonException::class);

        ValueObject::jsonDecode('{broken');
    }
}
