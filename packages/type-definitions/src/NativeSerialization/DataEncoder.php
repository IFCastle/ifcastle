<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\NativeSerialization;

use IfCastle\TypeDefinitions\DefinitionAwareInterface;
use IfCastle\TypeDefinitions\DefinitionStaticAwareInterface;
use IfCastle\TypeDefinitions\Exceptions\EncodingException;
use IfCastle\TypeDefinitions\TypeMixed;
use IfCastle\TypeDefinitions\Value\ContainerSerializableInterface;

final class DataEncoder
{
    /**
     * @throws EncodingException
     */
    public static function dataEncode(mixed $value): mixed
    {
        if ($value instanceof ContainerSerializableInterface) {
            return $value->containerSerialize();
        } elseif ($value instanceof DefinitionAwareInterface) {
            return $value->getDefinition()->encode($value);
        } elseif ($value instanceof DefinitionStaticAwareInterface) {
            return $value::definition()->encode($value);
        }

        throw new EncodingException(
            new TypeMixed('undefined'),
            'Expected instance of ContainerSerializableInterface|DefinitionAwareInterface|DefinitionStaticAwareInterface. Got {value}',
            ['value' => \get_debug_type($value)]
        );

    }
}
