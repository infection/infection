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

use Infection\TestFramework\Coverage\XmlReport\SourceFileInfoProvider;
use Infection\TestFramework\Coverage\XmlReport\XmlCoverageParser;
use Infection\TestFramework\Coverage\XmlReport\XPathFactory;
use Infection\Tests\Fixtures\TestFramework\PhpUnit\Coverage\XmlCoverageFixtures;
use Infection\Tests\TestFramework\Coverage\CoverageHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @group integration
 */
final class XmlCoverageParserTest extends TestCase
{
    /**
     * @var XmlCoverageParser
     */
    private $parser;

    protected function setUp(): void
    {
        $this->parser = new XmlCoverageParser();
    }

    /**
     * @dataProvider sourceFileInfoProviderProvider
     *
     * @param array<string, mixed> $expectedCoverage
     */
    public function test_it_reads_every_type_of_fixture(
        SourceFileInfoProvider $provider,
        array $expectedCoverage
    ): void {
        $fileData = $this->parser->parse($provider);

        $this->assertSame(
            $fileData->getSplFileInfo()->getRealPath(),
            $provider->provideFileInfo()->getRealPath()
        );

        $coverageData = $fileData->retrieveCoverageReport();

        $this->assertSame(
            $expectedCoverage,
            CoverageHelper::convertToArray([$coverageData])[0]
        );
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
            ->retrieveCoverageReport()
        ;

        $this->assertSame([], $coverageData->byLine);
        $this->assertSame([], $coverageData->byMethod);
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
            ->retrieveCoverageReport()
        ;

        $this->assertArrayHasKey(11, $coverageData->byLine);
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
            ->retrieveCoverageReport()
        ;

        $this->assertArrayNotHasKey(11, $coverageData->byLine);
    }

    public function sourceFileInfoProviderProvider(): iterable
    {
        foreach (XmlCoverageFixtures::provideAllFixtures() as $fixture) {
            yield [
                new SourceFileInfoProvider(
                    '/path/to/index.xml',
                    $fixture->coverageDir,
                    $fixture->relativeCoverageFilePath,
                    $fixture->projectSource
                ),
                $fixture->serializedCoverage,
            ];
        }
    }

    /**
     * @return SourceFileInfoProvider|MockObject
     */
    private function createSourceFileInfoProvider(string $xml)
    {
        $xPath = XPathFactory::createXPath($xml);

        $providerMock = $this->createMock(SourceFileInfoProvider::class);

        $providerMock
            ->expects($this->once())
            ->method('provideFileInfo')
            ->willReturn($this->createMock(SplFileInfo::class))
        ;

        $providerMock
            ->expects($this->once())
            ->method('provideXPath')
            ->willReturn($xPath)
        ;

        return $providerMock;
    }
}
