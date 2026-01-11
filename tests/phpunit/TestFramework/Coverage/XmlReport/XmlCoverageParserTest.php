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

use const DIRECTORY_SEPARATOR;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\Coverage\XmlReport\SourceFileInfoProvider;
use Infection\TestFramework\Coverage\XmlReport\XmlCoverageParser;
use Infection\TestFramework\SafeDOMXPath;
use Infection\TestFramework\Tracing\Trace\TestLocations;
use Infection\Tests\Fixtures\Finder\MockSplFileInfo;
use Infection\Tests\Fixtures\TestFramework\PhpUnit\Coverage\XmlCoverageFixture;
use Infection\Tests\Fixtures\TestFramework\PhpUnit\Coverage\XmlCoverageFixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use Symfony\Component\Filesystem\Path;

#[Group('integration')]
#[CoversClass(XmlCoverageParser::class)]
final class XmlCoverageParserTest extends TestCase
{
    private XmlCoverageParser $parser;

    protected function setUp(): void
    {
        $this->parser = new XmlCoverageParser();
    }

    #[DataProvider('lineCoverageProvider')]
    public function test_it_can_get_the_line_coverage(
        SafeDOMXPath $xPath,
        TestLocations $expected,
    ): void {
        $sourceFileInfoProviderStub = $this->createSourceFileInfoProviderStub($xPath);

        $actual = $this->parser
            ->parse($sourceFileInfoProviderStub)
            ->getTests();

        $this->assertEquals($expected, $actual);
    }

    public static function lineCoverageProvider(): iterable
    {
        yield 'coverage with empty percentage' => [
            SafeDOMXPath::fromString(
                <<<'XML'
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
                    XML,
                namespace: 'p',
            ),
            new TestLocations(),
        ];

        yield 'coverage with percent sign' => [
            SafeDOMXPath::fromString(
                <<<'XML'
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
                    XML,
                namespace: 'p',
            ),
            new TestLocations(
                byLine: [
                    11 => [
                        new TestLocation(
                            method: 'ExampleTest::test_it_just_works',
                            filePath: null,
                            executionTime: null,
                        ),
                    ],
                ],
            ),
        ];

        yield 'coverage with no covered lines' => [
            SafeDOMXPath::fromString(
                <<<'XML'
                    <phpunit xmlns="http://schema.phpunit.de/coverage/1.0">
                        <file name="secondLevel.php" path="/FirstLevel/SecondLevel">
                            <totals>
                                <lines total="1" comments="0" code="1" executable="1" executed="1" percent="100"/>
                            </totals>
                            <coverage>
                            </coverage>
                        </file>
                    </phpunit>
                    XML,
                namespace: 'p',
            ),
            new TestLocations(),
        ];

        // @phpstan-ignore argument.templateType
        yield from take(XmlCoverageFixtures::provideAllFixtures())
            ->map(self::createScenarioFromFixture(...))
            ->stream();
    }

    private function createSourceFileInfoProviderStub(SafeDOMXPath $xPath): SourceFileInfoProvider
    {
        $sourceFileInfoProviderStub = $this->createStub(SourceFileInfoProvider::class);
        $sourceFileInfoProviderStub
            ->method('provideFileInfo')
            ->willReturn(new MockSplFileInfo(''));
        $sourceFileInfoProviderStub
            ->method('provideXPath')
            ->willReturn($xPath);

        return $sourceFileInfoProviderStub;
    }

    /**
     * @return array{SafeDOMXPath, TestLocations}
     */
    private static function createScenarioFromFixture(XmlCoverageFixture $fixture): array
    {
        return [
            SafeDOMXPath::fromFile(
                Path::canonicalize(
                    $fixture->coverageDir . DIRECTORY_SEPARATOR . $fixture->relativeCoverageFilePath,
                ),
                namespace: 'p',
            ),
            $fixture->getTests(),
        ];
    }
}
