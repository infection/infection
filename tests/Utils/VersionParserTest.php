<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Utils;

use Infection\Utils\VersionParser;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class VersionParserTest extends TestCase
{
    /**
     * @var VersionParser
     */
    private $versionParser;

    protected function setUp(): void
    {
        $this->versionParser = new VersionParser();
    }

    /**
     * @dataProvider versionProvider
     */
    public function test_it_parses_version_from_string(string $content, string $expectedVersion): void
    {
        $result = $this->versionParser->parse($content);

        $this->assertSame($expectedVersion, $result);
    }

    public function test_it_throws_exception_when_content_has_no_version_substring(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->versionParser->parse('abc');
    }

    public function versionProvider()
    {
        return [
            ['phpspec version 1.2.3', '1.2.3'],
            ['PHPUnit 1.2.3 by Sebastian Bergmann and contributors.', '1.2.3'],
            ['1.2.3', '1.2.3'],
            ['10.20.13', '10.20.13'],
            ['a 1.2.3-patch b', '1.2.3-patch'],
            ['v1.2.3', '1.2.3'],
            ['6.5-abcde', '6.5-abcde'],
        ];
    }
}
