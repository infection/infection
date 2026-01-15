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

use function dirname;
use Exception;
use Infection\FileSystem\FakeFileSystem;
use Infection\Source\Exception\NoSourceFound;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageParser;
use Infection\TestFramework\Coverage\XmlReport\InvalidCoverage;
use Infection\TestFramework\Coverage\XmlReport\SourceFileInfoProvider;
use Infection\Tests\TestingUtility\FS;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use function Safe\file_put_contents;
use function Safe\unlink;
use function sprintf;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
#[CoversClass(IndexXmlCoverageParser::class)]
final class IndexXmlCoverageParserTest extends TestCase
{
    use ExpectsThrowables;

    private string $generatedIndexXmlPath;

    protected function setUp(): void
    {
        $this->generatedIndexXmlPath = FS::tmpFile('IndexXmlCoverageParserTest');
    }

    protected function tearDown(): void
    {
        unlink($this->generatedIndexXmlPath);
    }

    /**
     * @param list<SourceFileInfoProvider>|Exception $expected
     */
    #[DataProvider('indexProvider')]
    public function test_it_provides_file_information(
        string $pathname,
        string $coverageBasePath,
        array|Exception $expected,
    ): void {
        $parser = new IndexXmlCoverageParser(
            isSourceFiltered: false,
            fileSystem: new FakeFileSystem(),
        );

        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $actual = $parser->parse(
            $pathname,
            $coverageBasePath,
        );

        $actual = take($actual)->toAssoc();

        if (!($expected instanceof Exception)) {
            $this->assertEquals($expected, $actual);
        }
    }

    #[DataProvider('noCoveredLineReportProviders')]
    public function test_it_errors_when_no_lines_were_executed(string $xml): void
    {
        file_put_contents($this->generatedIndexXmlPath, $xml);

        $unfilteredParser = new IndexXmlCoverageParser(
            isSourceFiltered: false,
            fileSystem: new FakeFileSystem(),
        );

        $unfilteredNoSourceFound = $this->expectToThrow(
            fn () => $unfilteredParser->parse(
                $this->generatedIndexXmlPath,
                __DIR__,
            ),
        );

        $this->assertInstanceOf(NoSourceFound::class, $unfilteredNoSourceFound);
        $this->assertFalse($unfilteredNoSourceFound->isSourceFiltered);

        $filteredParser = new IndexXmlCoverageParser(
            isSourceFiltered: true,
            fileSystem: new FakeFileSystem(),
        );

        $filteredNoSourceFound = $this->expectToThrow(
            fn () => $filteredParser->parse(
                $this->generatedIndexXmlPath,
                __DIR__,
            ),
        );

        $this->assertInstanceOf(NoSourceFound::class, $filteredNoSourceFound);
        $this->assertTrue($filteredNoSourceFound->isSourceFiltered);
    }

    public static function indexProvider(): iterable
    {
        $nominalPhpUnitIndexPath = Path::canonicalize(__DIR__ . '/index.xml');

        $createNominalSourceFileInfo = static fn (
            string $relativeCoverageFilePath,
        ) => new SourceFileInfoProvider(
            coverageIndexPath: $nominalPhpUnitIndexPath,
            coverageDir: dirname($nominalPhpUnitIndexPath),
            relativeCoverageFilePath: $relativeCoverageFilePath,
            projectSource: '/path/to/src',
            fileSystem: new FakeFileSystem(),
        );

        yield 'nominal' => [
            $nominalPhpUnitIndexPath,
            dirname($nominalPhpUnitIndexPath),
            [
                $createNominalSourceFileInfo('FirstLevel/firstLevel.php.xml'),
                $createNominalSourceFileInfo('FirstLevel/SecondLevel/secondLevel.php.xml'),
                $createNominalSourceFileInfo('FirstLevel/SecondLevel/secondLevelTrait.php.xml'),
                $createNominalSourceFileInfo('zeroLevel.php.xml'),
                $createNominalSourceFileInfo('noPercentage.php.xml'),
            ],
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

        file_put_contents($this->generatedIndexXmlPath, $xml);

        $parser = new IndexXmlCoverageParser(
            isSourceFiltered: false,
            fileSystem: new FakeFileSystem(),
        );

        // Note that the result is lazy, hence the exception is not thrown (yet).
        $sources = $parser->parse(
            $this->generatedIndexXmlPath,
            __DIR__,
        );

        $this->expectExceptionObject(
            new InvalidCoverage(
                sprintf(
                    'Could not find the source attribute for the project in the file "%s".',
                    $this->generatedIndexXmlPath,
                ),
            ),
        );

        foreach ($sources as $source) {
            return;
        }
    }
}
