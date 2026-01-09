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

namespace Infection\Tests\TestFramework\Coverage\XmlReport\XmlCoverageParser;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\Coverage\JUnit\TestFileNameNotFoundException;
use Infection\TestFramework\Coverage\JUnit\TestFileTimeData;
use Infection\TestFramework\Coverage\XmlReport\SourceFileInfoProvider;
use Infection\TestFramework\Coverage\XmlReport\XmlCoverageParser;
use Infection\TestFramework\SafeDOMXPath;
use Infection\TestFramework\Tracing\Trace\SourceMethodLineRange;
use Infection\TestFramework\Tracing\Trace\TestLocations;
use Infection\Tests\Fixtures\Finder\MockSplFileInfo;
use Infection\Tests\Fixtures\TestFramework\PhpUnit\Coverage\XmlCoverageFixtures;
use Infection\Tests\TestFramework\Tracing\Trace\TestLocationsNormalizer;
use Infection\Tests\TestingUtility\PHPUnit\DataProviderFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function file_get_contents;

#[Group('integration')]
#[CoversClass(XmlCoverageParser::class)]
final class XmlCoverageParserTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/Fixtures';

    private XmlCoverageParser $parser;

    protected function setUp(): void
    {
        $this->parser = new XmlCoverageParser();
    }

    /**
     * @param array<string, mixed> $expected
     */
    #[DataProvider('lineCoverageProvider')]
    public function test_it_can_get_the_line_coverage(
        SafeDOMXPath $xPath,
        TestLocations $expected,
    ): void {
        $sourceFileInfoProviderStub = $this->createStub(SourceFileInfoProvider::class);
        $sourceFileInfoProviderStub
            ->method('provideFileInfo')
            ->willReturn(new MockSplFileInfo(''));
        $sourceFileInfoProviderStub
            ->method('provideXPath')
            ->willReturn($xPath);

        $actual = $this->parser
            ->parse($sourceFileInfoProviderStub)
            ->getTests();

        $this->assertEquals($expected, $actual);
    }

    public function test_it_reads_report_with_no_covered_lines(): void
    {
        $xml = <<<'XML'
            <?xml version="1.0"?>
            <phpunit xmlns="http://schema.phpunit.de/coverage/1.0">
                <file name="secondLevel.php" path="/FirstLevel/SecondLevel">
                    <totals>
                        <lines total="1" comments="0" code="1" executable="1" executed="1" percent="100"/>
                    </totals>
                    <coverage>
                    </coverage>
                </file>
            </phpunit>
            XML;

        $coverageData = $this->parser
            ->parse($this->createSourceFileInfoProvider($xml))
            ->getTests()
        ;

        $this->assertSame([], $coverageData->getTestsLocationsBySourceLine());
        $this->assertSame([], $coverageData->getSourceMethodRangeByMethod());
    }

    public function test_it_reads_report_with_percent_signs(): void
    {
        $xml = <<<'XML'
            <?xml version="1.0"?>
            <phpunit xmlns="http://schema.phpunit.de/coverage/1.0">
                <file name="secondLevel.php" path="/FirstLevel/SecondLevel">
                    <totals>
                        <lines total="1e7" comments="0" code="1" executable="1" executed="1" percent="1.0%"/>
                    </totals>
                    <coverage>
                        <line nr="11">
                            <covered by="ExampleTest::test_it_just_works"/>
                        </line>
                    </coverage>
                </file>
            </phpunit>
            XML;

        $coverageData = $this->parser
            ->parse($this->createSourceFileInfoProvider($xml))
            ->getTests()
        ;

        $this->assertArrayHasKey(11, $coverageData->getTestsLocationsBySourceLine());
    }

    public function test_it_reads_report_with_empty_percentage(): void
    {
        $xml = <<<'XML'
            <?xml version="1.0"?>
            <phpunit xmlns="http://schema.phpunit.de/coverage/1.0">
                <file name="secondLevel.php" path="/FirstLevel/SecondLevel">
                    <totals>
                        <lines total="1e7" comments="0" code="1" executable="1" executed="1" percent=""/>
                    </totals>
                    <coverage>
                        <line nr="11">
                            <covered by="ExampleTest::test_it_just_works"/>
                        </line>
                    </coverage>
                </file>
            </phpunit>
            XML;

        $coverageData = $this->parser
            ->parse($this->createSourceFileInfoProvider($xml))
            ->getTests()
        ;

        $this->assertArrayNotHasKey(11, $coverageData->getTestsLocationsBySourceLine());
    }

    public static function lineCoverageProvider(): iterable
    {
        yield from DataProviderFactory::prefix(
            '[PHPUnit 09] ',
            self::phpUnit09InfoProvider(),
        );
//
//        yield from DataProviderFactory::prefix(
//            '[PHPUnit 10] ',
//            self::phpUnit10InfoProvider(),
//        );
//
//        yield from DataProviderFactory::prefix(
//            '[PHPUnit 11] ',
//            self::phpUnit11InfoProvider(),
//        );
//
//        yield from DataProviderFactory::prefix(
//            '[PHPUnit 12] ',
//            self::phpUnit12InfoProvider(),
//        );
//
//        // https://codeception.com/docs/UnitTests
//        yield from DataProviderFactory::prefix(
//            '[Codeception (unit)] ',
//            self::codeceptionUnitProvider(),
//        );
//
//        // https://codeception.com/docs/BDD
//        yield from DataProviderFactory::prefix(
//            '[Codeception (BDD style)] ',
//            self::codeceptionBddProvider(),
//        );
//
//        // https://codeception.com/docs/AdvancedUsage#Cest-Classes
//        yield from DataProviderFactory::prefix(
//            '[Codeception (Cest style)] ',
//            self::codeceptionCestProvider(),
//        );
    }

    private static function phpUnit09InfoProvider(): iterable
    {
        yield 'covered class' => [
            SafeDOMXPath::fromFile(
                self::FIXTURES_DIR . '/phpunit-09-xml/Covered/Calculator.php.xml',
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    9 => [
                        new TestLocation(
                            'Infection\E2ETests\PHPUnit_09_3\Tests\Covered\CalculatorTest::test_add with data set #0',
                            null,
                            null,
                        ),
                    ],
                ],
                byMethod: [
                    'add' => new SourceMethodLineRange(7,10),
                ],
            ),
        ];
    }

    private function createSourceFileInfoProvider(string $xml): SourceFileInfoProvider&MockObject
    {
        $xPath = SafeDOMXPath::fromString($xml, 'p');

        $providerMock = $this->createMock(SourceFileInfoProvider::class);

        $providerMock
            ->expects($this->once())
            ->method('provideFileInfo')
            ->willReturn(new MockSplFileInfo(['file' => 'test.txt']))
        ;

        $providerMock
            ->expects($this->once())
            ->method('provideXPath')
            ->willReturn($xPath)
        ;

        return $providerMock;
    }
}
