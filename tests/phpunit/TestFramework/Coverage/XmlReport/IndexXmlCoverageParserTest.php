<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Coverage\XmlReport;

use function array_diff;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageParser;
use Infection\TestFramework\Coverage\XmlReport\NoLineExecuted;
use Infection\TestFramework\Coverage\XmlReport\SourceFileInfoProvider;
use Infection\Tests\Fixtures\TestFramework\PhpUnit\Coverage\XmlCoverageFixture;
use Infection\Tests\Fixtures\TestFramework\PhpUnit\Coverage\XmlCoverageFixtures;
use function iterator_to_array;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;
use function Safe\preg_replace;
use function Safe\realpath;
use function Safe\sprintf;
use function str_replace;
use Traversable;
use Webmozart\PathUtil\Path;

/**
 * @group integration
 */
final class IndexXmlCoverageParserTest extends TestCase
{
    /**
     * @var string|null
     */
    private static $xml;

    /**
     * @var IndexXmlCoverageParser
     */
    private $parser;

    protected function setUp(): void
    {
        $this->parser = new IndexXmlCoverageParser();
    }

    /**
     * @dataProvider coverageProvider
     */
    public function test_it_collects_data_recursively_for_all_files(string $xml): void
    {
        $sourceFilesData = $this->parser->parse(
            '/path/to/index.xml',
            $xml,
            XmlCoverageFixtures::FIXTURES_COVERAGE_DIR
        );

        // zeroLevel + noPercentage + firstLevel + secondLevel
        $this->assertCount(5, $sourceFilesData);
    }

    public function test_it_has_correct_coverage_data_for_each_file(): void
    {
        $sourceFilesData = $this->parser->parse(
            '/path/to/index.xml',
            preg_replace(
                '/percent=".*"/',
                '',
                self::getXml()
            ),
            XmlCoverageFixtures::FIXTURES_COVERAGE_DIR
        );

        $this->assertCoverageFixtureSame(
            XmlCoverageFixtures::provideFixtures(),
            $sourceFilesData
        );
    }

    public function test_it_correctly_parses_xml_when_directory_has_absolute_path_for_old_phpunit_versions(): void
    {
        $xml = <<<'XML'
<?xml version="1.0"?>
<phpunit xmlns="http://schema.phpunit.de/coverage/1.0">
  <build time="Mon Apr 10 20:06:19 GMT+0000 2017" phpunit="6.1.0" coverage="5.1.0">
    <runtime name="PHP" version="7.1.0" url="https://secure.php.net/"/>
    <driver name="xdebug" version="2.5.1"/>
  </build>
  <project source="/path/to/src">
    <tests>
      <test name="Infection\Tests\Mutator\ReturnValue\IntegerNegotiationTest::test_gets_mutation_reverses_integer_sign_when_positive" size="unknown" result="0" status="PASSED"/>
      <test name="Infection\Tests\Mutator\ReturnValue\IntegerNegotiationTest::testGetsMutationReversesIntegerSignWhenNegative" size="unknown" result="0" status="PASSED"/>
    </tests>
    <directory name="/absolute/path">
      <totals>
        <lines total="913" comments="130" code="783" executable="348" executed="7" percent="0"/>
      </totals>
    </directory>
  </project>
  <!-- The rest of the file has been removed for this test-->
</phpunit>
XML;

        $sourceFilesData = $this->parser->parse(
            '/path/to/index.xml',
            $xml,
            XmlCoverageFixtures::FIXTURES_COVERAGE_DIR
        );

        $this->assertCoverageFixtureSame([], $sourceFilesData);
    }

    public function test_it_has_correct_coverage_data_for_each_file_for_old_phpunit_versions(): void
    {
        $sourceFilesData = $this->parser->parse(
            '/path/to/index.xml',
            str_replace(
                '/path/to/src',
                realpath(XmlCoverageFixtures::FIXTURES_OLD_SRC_DIR),
                file_get_contents(XmlCoverageFixtures::FIXTURES_OLD_COVERAGE_DIR . '/index.xml')
            ),
            XmlCoverageFixtures::FIXTURES_OLD_COVERAGE_DIR
        );

        $this->assertCoverageFixtureSame(
            XmlCoverageFixtures::providePhpUnit6Fixtures(),
            $sourceFilesData
        );
    }

    /**
     * @dataProvider noCoveredLineReportProviders
     */
    public function test_it_errors_when_no_lines_were_executed(string $xml): void
    {
        $this->expectException(NoLineExecuted::class);

        $this->parser->parse(
            '/path/to/index.xml',
            $xml,
            XmlCoverageFixtures::FIXTURES_COVERAGE_DIR
        );
    }

    public function coverageProvider(): iterable
    {
        yield 'nominal' => [self::getXml()];

        yield 'PHPUnit <6' => [
            preg_replace(
                '/(source)(=\".*?\")/',
                'name$2',
                self::getXml()
            ),
        ];
    }

    public function noCoveredLineReportProviders(): iterable
    {
        yield 'zero lines executed' => [<<<'XML'
<?xml version="1.0"?>
<phpunit xmlns="http://schema.phpunit.de/coverage/1.0">
  <build time="Mon Apr 10 20:06:19 GMT+0000 2017" phpunit="6.1.0" coverage="5.1.0">
    <runtime name="PHP" version="7.1.0" url="https://secure.php.net/"/>
    <driver name="xdebug" version="2.5.1"/>
  </build>
  <project source="/path/to/src">
    <tests>
      <test name="Infection\Tests\Mutator\ReturnValue\IntegerNegotiationTest::test_gets_mutation_reverses_integer_sign_when_positive" size="unknown" result="0" status="PASSED"/>
      <test name="Infection\Tests\Mutator\ReturnValue\IntegerNegotiationTest::testGetsMutationReversesIntegerSignWhenNegative" size="unknown" result="0" status="PASSED"/>
    </tests>
    <directory name="/">
      <totals>
        <lines total="913" comments="130" code="783" executable="348" executed="0" percent="0"/>
      </totals>
    </directory>
  </project>
  <!-- The rest of the file has been removed for this test-->
</phpunit>
XML
        ];

        yield 'lines is not present' => [<<<'XML'
<?xml version="1.0"?>
<phpunit xmlns="http://schema.phpunit.de/coverage/1.0">
  <build time="Mon Apr 10 20:06:19 GMT+0000 2017" phpunit="6.1.0" coverage="5.1.0">
    <runtime name="PHP" version="7.1.0" url="https://secure.php.net/"/>
    <driver name="xdebug" version="2.5.1"/>
  </build>
  <project source="/path/to/src">
    <tests>
      <test name="Infection\Tests\Mutator\ReturnValue\IntegerNegotiationTest::test_gets_mutation_reverses_integer_sign_when_positive" size="unknown" result="0" status="PASSED"/>
      <test name="Infection\Tests\Mutator\ReturnValue\IntegerNegotiationTest::testGetsMutationReversesIntegerSignWhenNegative" size="unknown" result="0" status="PASSED"/>
    </tests>
    <directory name="/">
      <totals>
      </totals>
    </directory>
  </project>
  <!-- The rest of the file has been removed for this test-->
</phpunit>
XML
        ];
    }

    private static function getXml(): string
    {
        if (self::$xml !== null) {
            return self::$xml;
        }

        $xml = file_get_contents(XmlCoverageFixtures::FIXTURES_COVERAGE_DIR . '/index.xml');

        // Replaces dummy source path with the real path
        self::$xml = preg_replace(
            '/(source=\").*?(\")/',
            sprintf('$1%s$2', realpath(XmlCoverageFixtures::FIXTURES_SRC_DIR)),
            $xml
        );

        return self::$xml;
    }

    /**
     * @param iterable<XmlCoverageFixture> $coverageFixtures
     * @param iterable<SourceFileInfoProvider> $sourceFilesData
     */
    private function assertCoverageFixtureSame(
        iterable $coverageFixtures,
        iterable $sourceFilesData
    ): void {
        $this->assertSame([], array_diff(
            // Fixtures are not expected to be in any particular order
            iterator_to_array(self::xmlCoverageFixturesToList($coverageFixtures), false),
            iterator_to_array(self::sourceFileInfoProvidersToList($sourceFilesData), false),
        ));
    }

    /**
     * @param iterable<SourceFileInfoProvider> $sourceFilesData
     *
     * @return Traversable<string>
     */
    private static function sourceFileInfoProvidersToList(iterable $sourceFilesData): Traversable
    {
        foreach ($sourceFilesData as $provider) {
            yield $provider->provideFileInfo()->getPathname();
        }
    }

    /**
     * @param iterable<XmlCoverageFixture> $fixtures
     *
     * @return Traversable<string>
     */
    private static function xmlCoverageFixturesToList(iterable $fixtures): Traversable
    {
        foreach ($fixtures as $fixture) {
            yield $fixture->sourceFilePath;
        }
    }
}
