<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Utils;

use Infection\Utils\VersionParser;
use PHPUnit\Framework\TestCase;

class VersionParserTest extends TestCase
{
    /**
     * @var VersionParser
     */
    private $versionParser;

    protected function setUp()
    {
        $this->versionParser = new VersionParser();
    }

    /**
     * @dataProvider versionProvider
     */
    public function test_it_parses_version_from_string(string $content, string $expectedVersion)
    {
        $result = $this->versionParser->parse($content);

        $this->assertSame($expectedVersion, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_it_throws_exception_when_content_has_no_version_substring()
    {
        $this->versionParser->parse('abc');
    }

    public function versionProvider()
    {
        return [
            ['phpspec version 1.2.3', '1.2.3'],
            ['PHPUnit 1.2.3 by Sebastian Bergmann and contributors.', '1.2.3'],
            ['1.2.3', '1.2.3'],
            ['a 1.2.3-patch b', '1.2.3-patch'],
            ['v1.2.3', '1.2.3'],
        ];
    }
}