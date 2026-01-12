<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Value\ValueJson;
use IfCastle\TypeDefinitions\Value\ValueObject;
use PHPUnit\Framework\TestCase;

class TypeObjectTest extends TestCase
{
    public function testEncodeObject(): void
    {
        $typeObject = (new TypeObject('someName'))
            ->describe(new TypeInteger('number'))
            ->describe(new TypeFloat('float'))
            ->describe(new TypeBool('boolean'))
            ->describe(new TypeJson('json'))
            ->describe(new TypeString('description'));

        $data                       = [
            'number'                => 1,
            'float'                 => 1.1,
            'boolean'               => true,
            'json'                  => new ValueJson(['key1' => 'value', 'key2' => 567]),
            'description'           => 'This is a description',
        ];

        $encoded                    = $typeObject->encode($data);

        $this->assertIsArray($encoded, 'Encoded data is not an array');
        $this->assertArrayHasKey('number', $encoded, 'Key number is missing');
        $this->assertArrayHasKey('float', $encoded, 'Key float is missing');
        $this->assertArrayHasKey('boolean', $encoded, 'Key boolean is missing');
        $this->assertArrayHasKey('json', $encoded, 'Key json is missing');
        $this->assertArrayHasKey('description', $encoded, 'Key description is missing');

        $this->assertEquals(1, $encoded['number'], 'Number is not 1');
        $this->assertEquals(1.1, $encoded['float'], 'Float is not 1.1');
        $this->assertTrue($encoded['boolean'], 'Boolean is not true');
        $this->assertIsArray($encoded['json'], 'Json is not an array');
        $this->assertIsString($encoded['description'], 'Description is not a string');
    }

    public function testDecode(): void
    {
        $typeObject = (new TypeObject('someName'))
            ->describe(new TypeInteger('number'))
            ->describe(new TypeFloat('float'))
            ->describe(new TypeBool('boolean'))
            ->describe(new TypeJson('json'))
            ->describe(new TypeString('description'));

        $data                       = [
            'number'                => 1,
            'float'                 => 1.1,
            'boolean'               => true,
            'json'                  => ['key1' => 'value', 'key2' => 567],
            'description'           => 'This is a description',
        ];

        $decoded                    = $typeObject->decode($data);

        $this->assertInstanceOf(ValueObject::class, $decoded, 'Decoded data is not a ValueObject');
        $value                      = $decoded->getValue();

        $this->assertIsArray($value, 'Decoded data is not an array');
        $this->assertArrayHasKey('number', $value, 'Key number is missing');
        $this->assertArrayHasKey('float', $value, 'Key float is missing');
        $this->assertArrayHasKey('boolean', $value, 'Key boolean is missing');
        $this->assertArrayHasKey('json', $value, 'Key json is missing');
        $this->assertArrayHasKey('description', $value, 'Key description is missing');

        $this->assertEquals(1, $value['number'], 'Number is not 1');
        $this->assertEquals(1.1, $value['float'], 'Float is not 1.1');
        $this->assertTrue($value['boolean'], 'Boolean is not true');
        $this->assertInstanceOf(ValueJson::class, $value['json'], 'Json is not a ValueJson');
    }

}
