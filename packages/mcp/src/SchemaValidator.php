<?php

declare(strict_types=1);

namespace IfCastle\Mcp;

/**
 * Checks tool arguments against the subset of JSON Schema 2020-12 that hand-written tool schemas use: `type` (one
 * name or a list), `enum`, `properties`, `required`, `additionalProperties` (a boolean), `items`, `minItems`,
 * `maxItems`, `minLength`, `maxLength` (in code points), `pattern`, `minimum`, `maximum`, and the annotations
 * `title`, `description`, `default`, `examples` and `$comment`. A schema with any other keyword is refused when the
 * tool is created, so no constraint a tool states is silently skipped.
 *
 * Values are as json_decode() gives them without the associative flag: an object is a \stdClass and an array a list,
 * so `{}` and `[]` stay apart. An integer is a JSON number without a fraction or exponent.
 */
final class SchemaValidator
{
    private const array KEYWORDS = ['type', 'enum', 'properties', 'required', 'additionalProperties', 'items',
        'minItems', 'maxItems', 'minLength', 'maxLength', 'pattern', 'minimum', 'maximum', 'title', 'description',
        'default', 'examples', '$comment'];
    private const array TYPES = ['object', 'array', 'string', 'integer', 'number', 'boolean', 'null'];

    /**
     * @param array<string, mixed> $schema
     * @throws \InvalidArgumentException naming the first unsupported keyword, type or pattern
     */
    public static function assertSupported(array $schema, string $where): void
    {
        $unknown = \array_diff(\array_keys($schema), self::KEYWORDS);
        if ($unknown !== []) {
            throw new \InvalidArgumentException("$where: unsupported schema keyword " . \reset($unknown));
        }

        foreach ((array) ($schema['type'] ?? []) as $type) {
            if (!\in_array($type, self::TYPES, true)) {
                throw new \InvalidArgumentException("$where: unknown type " . \var_export($type, true));
            }
        }

        if (isset($schema['pattern']) && @\preg_match(self::regex($schema['pattern']), '') === false) {
            throw new \InvalidArgumentException("$where: invalid pattern");
        }

        foreach ((array) ($schema['properties'] ?? []) as $name => $property) {
            self::assertSupported($property, "$where.$name");
        }

        if (isset($schema['items'])) {
            self::assertSupported($schema['items'], $where . '[]');
        }
    }

    /**
     * @param array<string, mixed> $schema
     * @return ?string why $value fails $schema, naming its path; null when it passes
     */
    public static function check(array $schema, mixed $value, string $path = 'arguments'): ?string
    {
        if (isset($schema['type']) && !\in_array(self::typeOf($value), (array) $schema['type'], true)
            && !(self::typeOf($value) === 'integer' && \in_array('number', (array) $schema['type'], true))) {
            return "$path must be of type " . \implode(' or ', (array) $schema['type']);
        }

        if (isset($schema['enum']) && !\in_array($value, $schema['enum'], true)) {
            return "$path must be one of " . \json_encode($schema['enum'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return match (true) {
            $value instanceof \stdClass => self::checkObject($schema, $value, $path),
            \is_array($value)           => self::checkArray($schema, $value, $path),
            \is_string($value)          => self::checkString($schema, $value, $path),
            \is_int($value), \is_float($value) => self::checkNumber($schema, $value, $path),
            default                     => null,
        };
    }

    /**
     * @param array<string, mixed> $schema
     */
    private static function checkObject(array $schema, \stdClass $value, string $path): ?string
    {
        foreach ($schema['required'] ?? [] as $name) {
            if (!\property_exists($value, $name)) {
                return "$path.$name is required";
            }
        }

        // (array): a schema without properties may give them as {} to be sent as a JSON object
        $properties = (array) ($schema['properties'] ?? []);
        foreach (\get_object_vars($value) as $name => $item) {
            if (isset($properties[$name])) {
                $error = self::check($properties[$name], $item, "$path.$name");
                if ($error !== null) {
                    return $error;
                }
            } elseif (($schema['additionalProperties'] ?? true) === false) {
                return "$path.$name is not a known argument";
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $schema
     * @param list<mixed> $value
     */
    private static function checkArray(array $schema, array $value, string $path): ?string
    {
        if (\count($value) < ($schema['minItems'] ?? 0)) {
            return "$path must have at least {$schema['minItems']} items";
        }

        if (isset($schema['maxItems']) && \count($value) > $schema['maxItems']) {
            return "$path must have at most {$schema['maxItems']} items";
        }

        foreach (isset($schema['items']) ? $value : [] as $index => $item) {
            $error = self::check($schema['items'], $item, "{$path}[$index]");
            if ($error !== null) {
                return $error;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $schema
     */
    private static function checkString(array $schema, string $value, string $path): ?string
    {
        $length = \mb_strlen($value, 'UTF-8');
        if ($length < ($schema['minLength'] ?? 0)) {
            return "$path must be at least {$schema['minLength']} characters long";
        }

        if (isset($schema['maxLength']) && $length > $schema['maxLength']) {
            return "$path must be at most {$schema['maxLength']} characters long";
        }

        if (isset($schema['pattern']) && \preg_match(self::regex($schema['pattern']), $value) !== 1) {
            return "$path does not match the pattern {$schema['pattern']}";
        }

        return null;
    }

    /**
     * @param array<string, mixed> $schema
     */
    private static function checkNumber(array $schema, int|float $value, string $path): ?string
    {
        if (isset($schema['minimum']) && $value < $schema['minimum']) {
            return "$path must be at least {$schema['minimum']}";
        }

        if (isset($schema['maximum']) && $value > $schema['maximum']) {
            return "$path must be at most {$schema['maximum']}";
        }

        return null;
    }

    private static function typeOf(mixed $value): string
    {
        return match (true) {
            $value instanceof \stdClass => 'object',
            \is_array($value)           => 'array',
            \is_string($value)          => 'string',
            \is_int($value)             => 'integer',
            \is_float($value)           => 'number',
            \is_bool($value)            => 'boolean',
            default                     => 'null',
        };
    }

    /** An ECMA 262 pattern as PCRE: unanchored, as JSON Schema matches it, over code points. */
    private static function regex(string $pattern): string
    {
        return '/' . \str_replace('/', '\/', $pattern) . '/u';
    }
}
