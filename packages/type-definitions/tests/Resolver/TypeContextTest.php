<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Resolver;

use IfCastle\TypeDefinitions\FromEnv;
use IfCastle\TypeDefinitions\Type;
use IfCastle\TypeDefinitions\TypeInternal;
use PHPUnit\Framework\TestCase;

readonly class DerivedTypeAttribute extends Type {}

final class TypeContextTest extends TestCase
{
    public function testExactAttributeAndSubclassAreBothFound(): void
    {
        $environment = new FromEnv(key: 'request');
        $exact = new Type(new TypeInternal('request', \stdClass::class));
        $context = new TypeContext(attributes: [$environment, $exact]);

        self::assertSame($exact, $context->getAttribute(Type::class));
        self::assertTrue($context->hasAttribute(Type::class));
        self::assertSame($environment, $context->getAttribute(FromEnv::class));
        self::assertTrue($context->hasAttribute(FromEnv::class));
        self::assertNull($context->getAttribute(DerivedTypeAttribute::class));
        self::assertFalse($context->hasAttribute(DerivedTypeAttribute::class));

        $derived = new DerivedTypeAttribute(new TypeInternal('request', \stdClass::class));
        $subclassContext = new TypeContext(attributes: [$derived]);
        self::assertSame($derived, $subclassContext->getAttribute(Type::class));
        self::assertTrue($subclassContext->hasAttribute(Type::class));
    }
}
