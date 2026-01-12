<?php

declare(strict_types=1);

namespace IfCastle\DI;

use IfCastle\DI\Dependencies\ChildClassWithAttributes;
use IfCastle\DI\Dependencies\ClassWithScalarDependencies;
use IfCastle\DI\Dependencies\InjectableClass;
use IfCastle\DI\Dependencies\SomeClass;
use IfCastle\DI\Dependencies\UseConstructorInterface;
use PHPUnit\Framework\TestCase;

class AttributesToDescriptorsTest extends TestCase
{
    public function testParameterToDescriptor(): void
    {
        $descriptors                = AttributesToDescriptors::readDescriptors(SomeClass::class);

        $this->assertIsArray($descriptors);

        $descriptor                 = $descriptors[0];

        $this->assertInstanceOf(DescriptorInterface::class, $descriptor);
        $this->assertInstanceOf(Dependency::class, $descriptor);
        $this->assertEquals('some', $descriptor->key);
        $this->assertEquals(UseConstructorInterface::class, $descriptor->type);
        $this->assertTrue($descriptor->isRequired);
        $this->assertEquals('', $descriptor->property);
        $this->assertFalse($descriptor->isLazy);
    }

    public function testInheritanceAndAttributes(): void
    {
        $descriptors                = AttributesToDescriptors::readDescriptors(ChildClassWithAttributes::class);

        $this->assertIsArray($descriptors);
        $this->assertCount(3, $descriptors);

        $some                       = $descriptors[0];

        $this->assertInstanceOf(DescriptorInterface::class, $some);
        $this->assertInstanceOf(Dependency::class, $some);
        $this->assertSame(Dependency::class, $some::class);
        $this->assertEquals('some', $some->key);
        $this->assertEquals(UseConstructorInterface::class, $some->type);
        $this->assertTrue($some->isRequired);
        $this->assertEquals('', $some->property);
        $this->assertFalse($some->isLazy);

        $someString                 = $descriptors[1];

        $this->assertInstanceOf(DescriptorInterface::class, $someString);
        $this->assertInstanceOf(Dependency::class, $someString);
        $this->assertSame(Dependency::class, $someString::class);
        $this->assertEquals('someString', $someString->key);
        $this->assertEquals('string', $someString->type);
        $this->assertTrue($someString->isRequired);
        $this->assertEquals('', $someString->property);
        $this->assertFalse($someString->isLazy);

        $someInt                    = $descriptors[2];

        $this->assertInstanceOf(DescriptorInterface::class, $someInt);
        $this->assertInstanceOf(Dependency::class, $someInt);
        $this->assertSame(Dependency::class, $someInt::class);
        $this->assertEquals('someInt', $someInt->key);
        $this->assertEquals('int', $someInt->type);
        $this->assertFalse($someInt->isRequired, 'int is not required');
        $this->assertEquals('', $someInt->property);
        $this->assertFalse($someInt->isLazy);
    }

    public function testPropertyToDescriptor(): void
    {
        $descriptors                = AttributesToDescriptors::readDescriptors(InjectableClass::class);

        $this->assertIsArray($descriptors);
        $this->assertCount(3, $descriptors);

        $required                   = $descriptors[0];

        $this->assertInstanceOf(DescriptorInterface::class, $required);
        $this->assertInstanceOf(Dependency::class, $required);
        $this->assertEquals('required', $required->key);
        $this->assertEquals(UseConstructorInterface::class, $required->type);
        $this->assertTrue($required->isRequired);
        $this->assertEquals('required', $required->property);
        $this->assertFalse($required->isLazy);

        $optional                   = $descriptors[1];

        $this->assertInstanceOf(DescriptorInterface::class, $optional);
        $this->assertInstanceOf(Dependency::class, $optional);
        $this->assertEquals('optional', $optional->key);
        $this->assertEquals(UseConstructorInterface::class, $optional->type);
        $this->assertFalse($optional->isRequired);
        $this->assertEquals('optional', $optional->property);
        $this->assertFalse($optional->isLazy);

        $lazy                       = $descriptors[2];

        $this->assertInstanceOf(DescriptorInterface::class, $lazy);
        $this->assertInstanceOf(Dependency::class, $lazy);
        $this->assertEquals('lazy', $lazy->key);
        $this->assertEquals(UseConstructorInterface::class, $lazy->type);
        $this->assertTrue($lazy->isRequired);
        $this->assertEquals('lazy', $lazy->property);
        $this->assertTrue($lazy->isLazy);
    }

    public function testScalarParameters(): void
    {
        $descriptors                = AttributesToDescriptors::readDescriptors(ClassWithScalarDependencies::class);

        $this->assertIsArray($descriptors);

        foreach ($descriptors as $descriptor) {
            $this->assertInstanceOf(DescriptorInterface::class, $descriptor);
            $this->assertInstanceOf(Dependency::class, $descriptor);
            $this->assertInstanceOf(FromConfig::class, $descriptor);

            $this->assertStringContainsString('class_with_scalar_dependencies', $descriptor->getKey());
        }
    }
}
