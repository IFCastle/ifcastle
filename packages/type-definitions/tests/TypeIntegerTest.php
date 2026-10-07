<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use IfCastle\TypeDefinitions\Exceptions\DefinitionIsNotValid;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TypeIntegerTest extends TestCase
{
    public static function validIntegers(): array
    {
        return [[0, 0], [42, 42], ['0', 0], ['-42', -42], [(string) PHP_INT_MAX, PHP_INT_MAX], [(string) PHP_INT_MIN, PHP_INT_MIN]];
    }

    #[DataProvider('validIntegers')]
    public function testDecodesExactIntegers(int|string $input, int $expected): void
    {
        $this->assertSame($expected, new TypeInteger('value')->decode($input));
        $this->assertNull(new TypeInteger('value')->validate($input, isThrow: false));
    }

    public static function invalidIntegers(): array
    {
        return [['1.5'], ['1e0'], ['abc'], [' 1'], ['1 '], [''], ['01'], ['+1'],
            [(string) PHP_INT_MAX . '0'], [(string) PHP_INT_MIN . '0'], [true], [1.5], [[]]];
    }

    #[DataProvider('invalidIntegers')]
    public function testRejectsLossyOrMalformedIntegers(mixed $input): void
    {
        $this->assertNotNull(new TypeInteger('value')->validate($input, isThrow: false));
        $this->expectException(DefinitionIsNotValid::class);
        new TypeInteger('value')->decode($input);
    }
}
