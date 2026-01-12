<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions\Value;

use IfCastle\TypeDefinitions\DefinitionMutableInterface;
use IfCastle\TypeDefinitions\TypeString;

class ValueString extends ValueContainer
{
    /**
     * Convert camel style to snake.
     *
     *
     */
    public static function camelToSnake(string $string): string
    {
        if (\preg_match('#[A-Z]#', $string) === 0) {
            return $string;
        }

        return \strtolower(
            (string) \preg_replace_callback(
                '#([a-z])([A-Z])#',
                static fn($m) => $m[1] . '_' . $m[2],
                $string
            )
        );
    }

    /**
     * Convert snake style to camel.
     *
     *
     */
    public static function snakeToCamel(string $string): string
    {
        return \str_replace('_', '', \ucwords($string, '_'));
    }


    public static function snakeToCamelFirstLow(string $string): string
    {
        return \lcfirst(\str_replace('_', '', \ucwords($string, '_')));
    }

    #[\Override]
    public static function definition(): DefinitionMutableInterface
    {
        return new TypeString('string');
    }
}
