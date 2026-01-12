<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

final class PhpdocDescriptionParser
{
    /**
     * @var array<string>
     */
    public const array DOC_TAGS      = ['access', 'see', 'since', 'deprecated',
        'link', 'author', 'version', 'category', 'package'];

    /**
     * @return array<string>
     */
    public static function getDescription(string $doc): array
    {
        $data                       = self::extractNotations($doc);
        $description                = [];

        foreach ($data as $row) {
            if (!empty($row['description'])) {

                // If description started with spase + * then remove it
                $text               = \preg_replace('/^\s*\*\s*/', '', \trim((string) $row['description']));

                if ($description === [] && empty($text)) {
                    continue;
                }

                if ($text !== '/**' && $text !== '/') {
                    $description[]  = $text;
                }
            } elseif (!empty($row['tag']) && \in_array($row['tag'], self::DOC_TAGS, true)) {
                $description[]  = $row['tag'] . (empty($row['value']) ? '' : ': ' . $row['value']);
            }
        }

        // Remove empty lines from the end of the description
        while ($description !== []) {
            if ($description[\count($description) - 1] === '') {
                \array_pop($description);
            } else {
                break;
            }
        }

        return $description;
    }

    /**
     * @return array<array{description: string, tag: array<string, string>, value: string, multiline_value: string}>
     */
    protected static function extractNotations(string $doc): array
    {
        $matches                    = null;

        $description                = '(?<description>\S.*?)';
        $tag                        = '\s*@(?<tag>\S+)(?:\h+(?<value>\S.*?)|\h*)';
        $tagContinue                = '(?:\040){2}(?<multiline_value>\S.*?)';
        $regex                      = '/^\s*(?:(?:\/\*)?\*)?(?:' . $tag . '|' . $tagContinue . '|' . $description . ')(?:\s*\*\*\/)?\r?$/m';

        /* @phpstan-ignore-next-line */
        return \preg_match_all($regex, $doc, $matches, PREG_SET_ORDER) ? $matches : [];
    }
}
