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

namespace Infection\Tests\TestFramework\Coverage\XmlReport\IndexXmlCoverageParser;

use function array_diff;
use Infection\Source\Exception\NoSourceFound;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageParser;
use Infection\TestFramework\Coverage\XmlReport\InvalidCoverage;
use Infection\TestFramework\Coverage\XmlReport\SourceFileInfoProvider;
use Infection\Tests\Fixtures\TestFramework\PhpUnit\Coverage\XmlCoverageFixture;
use Infection\Tests\Fixtures\TestFramework\PhpUnit\Coverage\XmlCoverageFixtures;
use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;
use function Safe\preg_replace;
use function sprintf;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Traversable;

#[Group('integration')]
#[CoversClass(IndexXmlCoverageParser::class)]
final class IndexXmlCoverageParserTest extends TestCase
{
    private static ?string $fixturesXmlFileName = null;

    private static ?string $fixturesOldXmlFileName = null;

    private Filesystem $filesystem;

    private IndexXmlCoverageParser $parser;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->parser = new IndexXmlCoverageParser(false);
    }

    #[DataProvider('coverageProvider')]
    public function test_it_collects_data_recursively_for_all_files(
        string $coverageIndexPath,
        string $coverageBasePath,
    ): void {
        $sourceFilesData = $this->parser->parse(
            $coverageIndexPath,
            $coverageBasePath,
        );

        // zeroLevel + noPercentage + firstLevel + secondLevel
        $this->assertCount(5, [...$sourceFilesData]);
    }

    public function test_it_has_correct_coverage_data_for_each_file(): void
    {
        $sourceFilesData = $this->parser->parse(
            self::getFixturesXmlFileName(),
            XmlCoverageFixtures::FIXTURES_COVERAGE_DIR,
        );

        $this->assertCoverageFixtureSame(
            XmlCoverageFixtures::provideFixtures(),
            $sourceFilesData,
        );
    }

    public function test_it_correctly_parses_xml_when_directory_has_absolute_path_for_old_phpunit_versions(): void
    {
        $sourceFilesData = $this->parser->parse(
            __DIR__ . '/phpunit6_index_with_absolute_path.xml',
            __DIR__,
        );

        $this->assertCoverageFixtureSame([], $sourceFilesData);
    }

    public function test_it_has_correct_coverage_data_for_each_file_for_old_phpunit_versions(): void
    {
        $sourceFilesData = $this->parser->parse(
            self::getOldFixturesXmlFileName(),
            XmlCoverageFixtures::FIXTURES_OLD_COVERAGE_DIR,
        );

        $this->assertCoverageFixtureSame(
            XmlCoverageFixtures::providePhpUnit6Fixtures(),
            $sourceFilesData,
        );
    }

    #[DataProvider('noCoveredLineReportProviders')]
    public function test_it_errors_when_no_lines_were_executed(string $xml): void
    {
        $filename = __DIR__ . '/generated_index.xml';
        $this->filesystem->dumpFile($filename, $xml);

        $this->expectException(NoSourceFound::class);

        $this->parser->parse(
            $filename,
            __DIR__,
        );
    }

    #[DataProvider('noCoveredLineReportProviders')]
    public function test_it_errors_for_git_diff_lines_mode_when_no_lines_were_executed(string $xml): void
    {
        $filename = __DIR__ . '/generated_index.xml';
        $this->filesystem->dumpFile($filename, $xml);

        $this->expectException(NoSourceFound::class);

        (new IndexXmlCoverageParser(true))->parse(
            $filename,
            __DIR__,
        );
    }

    public static function coverageProvider(): iterable
    {
        yield 'nominal' => [
            Path::canonicalize(__DIR__ . '/index.xml'),
            __DIR__,
        ];

        yield 'PHPUnit <6' => [
            Path::canonicalize(__DIR__ . '/index-for_phpunit6_and_less.xml'),
            __DIR__,
        ];
    }

    public static function noCoveredLineReportProviders(): iterable
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

    public function test_it_errors_when_no_phpunit_project_source_could_be_found(): void
    {
        $xml = <<<'XML'
            <?xml version="1.0"?>
                <phpunit xmlns="http://schema.phpunit.de/coverage/1.0">
                  <build time="Mon Apr 10 20:06:19 GMT+0000 2017" phpunit="6.1.0" coverage="5.1.0">
                    <runtime name="PHP" version="7.1.0" url="https://secure.php.net/"/>
                    <driver name="xdebug" version="2.5.1"/>
                  </build>
                  <project>
                    <tests>
                      <test name="Infection\Tests\Mutator\ReturnValue\IntegerNegotiationTest::test_gets_mutation_reverses_integer_sign_when_positive" size="unknown" result="0" status="PASSED"/>
                      <test name="Infection\Tests\Mutator\ReturnValue\IntegerNegotiationTest::testGetsMutationReversesIntegerSignWhenNegative" size="unknown" result="0" status="PASSED"/>
                    </tests>
                    <directory name="/">
                      <totals>
                        <lines total="913" comments="130" code="783" executable="348" executed="24" percent="6.90"/>
                      </totals>
                    </directory>
                  </project>
                  <!-- The rest of the file has been removed for this test-->
                </phpunit>
            XML;

        $filename = __DIR__ . '/generated_index.xml';
        $this->filesystem->dumpFile($filename, $xml);

        // Note that the result is lazy, hence the exception is not thrown (yet).
        $sources = $this->parser->parse(
            $filename,
            __DIR__,
        );

        $this->expectExceptionObject(
            new InvalidCoverage(
                sprintf(
                    'Could not find the source attribute for the project in the file "%s".',
                    $filename,
                ),
            ),
        );

        foreach ($sources as $source) {
            return;
        }
    }

    private static function getFixturesXmlFileName(): string
    {
        if (self::$fixturesXmlFileName !== null) {
            return self::$fixturesXmlFileName;
        }

        $sourceXml = Path::canonicalize(XmlCoverageFixtures::FIXTURES_COVERAGE_DIR . '/index.xml');

        $xml = file_get_contents($sourceXml);

        // Replaces dummy source path with the real path
        $correctedXml = preg_replace(
            '/(source=\").*?(\")/',
            sprintf(
                '$1%s$2',
                Path::canonicalize(XmlCoverageFixtures::FIXTURES_SRC_DIR),
            ),
            $xml,
        );

        self::$fixturesXmlFileName = Path::canonicalize(XmlCoverageFixtures::FIXTURES_COVERAGE_DIR . '/generated_index.xml');

        (new Filesystem())->dumpFile(self::$fixturesXmlFileName, $correctedXml);

        return self::$fixturesXmlFileName;
    }

    private static function getOldFixturesXmlFileName(): string
    {
        if (self::$fixturesOldXmlFileName !== null) {
            return self::$fixturesOldXmlFileName;
        }

        $sourceXml = Path::canonicalize(XmlCoverageFixtures::FIXTURES_OLD_COVERAGE_DIR . '/index.xml');

        $xml = file_get_contents($sourceXml);

        // Replaces dummy source path with the real path
        $correctedXml = preg_replace(
            '/(name=\").*?(\")/',
            sprintf(
                '$1%s$2',
                Path::canonicalize(XmlCoverageFixtures::FIXTURES_OLD_SRC_DIR),
            ),
            $xml,
        );

        self::$fixturesOldXmlFileName = Path::canonicalize(XmlCoverageFixtures::FIXTURES_OLD_COVERAGE_DIR . '/generated_index.xml');

        (new Filesystem())->dumpFile(self::$fixturesOldXmlFileName, $correctedXml);

        return self::$fixturesOldXmlFileName;
    }

    /**
     * @param iterable<XmlCoverageFixture> $coverageFixtures
     * @param iterable<SourceFileInfoProvider> $sourceFilesData
     */
    private function assertCoverageFixtureSame(
        iterable $coverageFixtures,
        iterable $sourceFilesData,
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
