<?php

declare(strict_types=1);

namespace IfCastle\TypeDefinitions;

use PHPUnit\Framework\TestCase;

class PhpdocDescriptionParserTest extends TestCase
{
    public function testParseBasic(): void
    {
        $result = PhpdocDescriptionParser::getDescription('
            /**
             * Returns a list of interfaces that are used for binding this service.
             *
             * @return string[]
             */');

        $this->assertEquals(['Returns a list of interfaces that are used for binding this service.'], $result);
    }

    public function testParseMultipleLines(): void
    {
        $result                     = PhpdocDescriptionParser::getDescription('
            /**
             * The method returns a list of Tags in which the service is visible.
             * And this is the second line.
             *
             *
             * @param string $param This is a parameter1.
             * @param array<int, float> $param2 This is a parameter2.
             *
             * @throws \Exception
             * @return string[]
             */');

        $this->assertEquals(['The method returns a list of Tags in which the service is visible.', 'And this is the second line.'], $result);
    }

    public function testParseSpecialTags(): void
    {
        $result                     = PhpdocDescriptionParser::getDescription('
            /**
             * The method with special tags.
             * @access public
             * @deprecated
             * @link http://example.com
             */');

        $this->assertEquals(['The method with special tags.', 'access: public', 'deprecated', 'link: http://example.com'], $result);
    }
}
