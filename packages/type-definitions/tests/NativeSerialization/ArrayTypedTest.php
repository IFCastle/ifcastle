<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\NativeSerialization;

use PHPUnit\Framework\TestCase;

/** The validator given to ArrayTyped reaches the object's own toArray() and fromArray(), which write its members. */
class ArrayTypedTest extends TestCase
{
    public function testTheValidatorIsPassedToTheObject(): void
    {
        $validator                  = new class implements ArraySerializableValidatorInterface {
            public function isSerializationAllowed(?object $object = null): bool
            {
                return true;
            }

            public function isUnSerializationAllowed(string $type): bool
            {
                return true;
            }
        };

        $array                      = ArrayTyped::serialize(new ValidatorRecorder(), $validator);
        $this->assertTrue($array['withValidator']);

        ArrayTyped::unserialize($array, $validator);
        $this->assertSame($validator, ValidatorRecorder::$restoredWith);

        $this->assertTrue(ArrayTyped::serializeList($validator, new ValidatorRecorder())[0]['withValidator']);
    }
}

final class ValidatorRecorder implements ArraySerializableInterface
{
    public static ?ArraySerializableValidatorInterface $restoredWith = null;

    #[\Override]
    public static function fromArray(array $array, ?ArraySerializableValidatorInterface $validator = null): static
    {
        self::$restoredWith         = $validator;

        return new self();
    }

    #[\Override]
    public function toArray(?ArraySerializableValidatorInterface $validator = null): array
    {
        return ['withValidator' => $validator !== null];
    }
}
