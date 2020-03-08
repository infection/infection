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

namespace Infection\Tests\TestFramework\PhpUnit\Coverage;

use Infection\TestFramework\Coverage\CoverageFileData;
use Infection\TestFramework\PhpUnit\Coverage\SourceFileInfoProvider;
use Infection\TestFramework\PhpUnit\Coverage\XmlCoverageParser;
use Infection\TestFramework\PhpUnit\Coverage\XPathFactory;
use Infection\Tests\TestFramework\Coverage\CoverageHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @group integration
 * @covers \Infection\TestFramework\PhpUnit\Coverage\XmlCoverageParser
 */
final class XmlCoverageParserTest extends TestCase
{
    public static function sourceFileInfoProviderProvider(): iterable
    {
        foreach (XmlCoverageFixtures::provideAllFixtures() as $fixture) {
            yield [
                new SourceFileInfoProvider(
                    $fixture->coverageDir,
                    $fixture->relativeCoverageFilePath,
                    $fixture->projectSource
                ),
                $fixture->serializedCoverage,
                $fixture->sourceFilePath,
            ];
        }
    }

    /**
     * @dataProvider sourceFileInfoProviderProvider
     *
     * @param array<string, mixed> $expectedCoverage
     */
    public function test_it_reads_every_type_of_fixture(
        SourceFileInfoProvider $provider,
        array $expectedCoverage,
        string $sourceFilePath
    ): void {
        $parser = new XmlCoverageParser($provider);
        $fileData = $parser->parse();

        $this->assertSame($fileData->getSplFileInfo()->getRealPath(), $provider->provideFileInfo()->getRealPath());

        $coverageData = $fileData->retrieveCoverageFileData();

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

        $coverageData = $this->parseXml($xml);

        $this->assertSame([], $coverageData->byLine);
        $this->assertSame([], $coverageData->byMethod);
    }

    private function parseXml(string $xml): CoverageFileData
    {
        $xPath = XPathFactory::createXPath($xml);

        $this->assertSame(0, $xPath->query('/phpunit/file/coverage/line')->length);

        $providerMock = $this->createMock(SourceFileInfoProvider::class);

        $providerMock
            ->expects($this->once())
            ->method('provideFileInfo')
            ->willReturn($this->createMock(SplFileInfo::class));

        $providerMock
            ->expects($this->once())
            ->method('provideXPath')
            ->willReturn($xPath);

        $parser = new XmlCoverageParser($providerMock);
        $fileData = $parser->parse();

        return $fileData->retrieveCoverageFileData();
    }
}
