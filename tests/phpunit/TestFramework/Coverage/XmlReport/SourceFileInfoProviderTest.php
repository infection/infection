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

use Infection\TestFramework\Coverage\XmlReport\InvalidCoverage;
use Infection\TestFramework\Coverage\XmlReport\SourceFileInfoProvider;
use Infection\Tests\Fixtures\TestFramework\PhpUnit\Coverage\XmlCoverageFixtures;
use PHPUnit\Framework\TestCase;
use function Safe\sprintf;
use Webmozart\PathUtil\Path;

/**
 * @group integration
 */
final class SourceFileInfoProviderTest extends TestCase
{
    /**
     * @dataProvider fileFixturesProvider
     */
    public function test_it_provides_file_info_and_xpath(
        string $coverageDir,
        string $relativeCoverageFilePath,
        string $projectSource,
        string $expectedSourceFilePath
    ): void {
        $provider = new SourceFileInfoProvider(
            '/path/to/index.xml',
            $coverageDir,
            $relativeCoverageFilePath,
            $projectSource
        );

        $this->assertSame($expectedSourceFilePath, $provider->provideFileInfo()->getRealPath());

        $xPath = $provider->provideXPath();

        $xPathAgain = $provider->provideXPath();

        $this->assertSame($xPath, $xPathAgain);
    }

    public function test_it_errors_when_the_xml_file_could_not_be_found(): void
    {
        $provider = new SourceFileInfoProvider(
            '/path/to/index.xml',
            '/path/to/coverage-dir',
            'zeroLevel.php.xml',
            'projectSource'
        );

        try {
            $provider->provideFileInfo();

            $this->fail();
        } catch (InvalidCoverage $exception) {
            $this->assertSame(
                'Could not find the XML coverage file '
                . '"/path/to/coverage-dir/zeroLevel.php.xml" listed in "/path/to/index.xml". Make '
                . 'sure the coverage used is up to date',
                $exception->getMessage()
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertNull($exception->getPrevious());
        }
    }

    public function test_it_errors_when_the_source_file_could_not_be_found(): void
    {
        $incorrectCoverageSrcDir = Path::canonicalize(XmlCoverageFixtures::FIXTURES_INCORRECT_COVERAGE_DIR . '/src');

        $provider = new SourceFileInfoProvider(
            '/path/to/index.xml',
            XmlCoverageFixtures::FIXTURES_COVERAGE_DIR,
            'zeroLevel.php.xml',
            $incorrectCoverageSrcDir
        );

        try {
            $provider->provideFileInfo();

            $this->fail();
        } catch (InvalidCoverage $exception) {
            $this->assertSame(
                sprintf(
                    'Could not find the source file "%s/zeroLevel.php" referred by '
                    . '"%s/zeroLevel.php.xml". Make sure the coverage used is up to date',
                    $incorrectCoverageSrcDir,
                    Path::canonicalize(XmlCoverageFixtures::FIXTURES_COVERAGE_DIR)
                ),
                $exception->getMessage()
            );
            $this->assertSame(0, $exception->getCode());
            $this->assertNull($exception->getPrevious());
        }
    }

    public function fileFixturesProvider(): iterable
    {
        foreach (XmlCoverageFixtures::provideAllFixtures() as $fixture) {
            yield [
                $fixture->coverageDir,
                $fixture->relativeCoverageFilePath,
                $fixture->projectSource,
                $fixture->sourceFilePath,
            ];
        }
    }
}
