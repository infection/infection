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

use Infection\FileSystem\FileSystem;
use Infection\TestFramework\Coverage\XmlReport\InvalidCoverage;
use Infection\TestFramework\Coverage\XmlReport\SourceFileInfoProvider;
use Infection\Tests\Fixtures\TestFramework\PhpUnit\Coverage\XmlCoverageFixtures;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Path;
use ValueError;

#[Group('integration')]
#[CoversClass(SourceFileInfoProvider::class)]
final class SourceFileInfoProviderTest extends TestCase
{
    #[DataProvider('fileFixturesProvider')]
    public function test_it_provides_file_info_and_xpath(
        string $coverageIndexPath,
        string $coverageDir,
        string $relativeCoverageFilePath,
        string $projectSource,
        string $expectedSourceFilePath,
    ): void {
        // We configure the FileSystem so that the computed XML file needs to point to a real file
        // but the source file does not need to exist.
        $fileSystemMock = $this->createMock(FileSystem::class);
        $fileSystemMock
            ->expects($this->once())
            ->method('isReadableFile')
            ->willReturn(true);
        $fileSystemMock
            ->expects($this->once())
            ->method('readFile')
            ->willReturnCallback(file_get_contents(...));
        $fileSystemMock
            ->expects($this->once())
            ->method('realPath')
            ->with($expectedSourceFilePath)
            ->willReturn($expectedSourceFilePath);

        $provider = new SourceFileInfoProvider(
            $coverageIndexPath,
            $coverageDir,
            $relativeCoverageFilePath,
            $projectSource,
            $fileSystemMock,
        );

        // Note that in practice we care about the real path, not the pathname.
        // However, since we are creating a real SplFileInfo instance and the
        // path is a fake one, `getRealPath()` would return `false`.
        $actualSourceFilePath = $provider->provideFileInfo()->getPathname();

        $this->assertSame($expectedSourceFilePath, $actualSourceFilePath);

        // We cannot check that the XPath is correct... Only that we produced
        // a cached XPath.
        $xPath = $provider->provideXPath();
        $xPathAgain = $provider->provideXPath();

        $this->assertSame($xPath, $xPathAgain);
    }

    public function test_it_errors_when_the_xml_file_could_not_be_found(): void
    {
        $fileSystemMock = $this->createMock(FileSystem::class);
        $fileSystemMock
            ->expects($this->once())
            ->method('isReadableFile')
            ->willReturn(false);

        $provider = new SourceFileInfoProvider(
            '/path/to/index.xml',
            '/path/to/coverage-dir',
            'zeroLevel.php.xml',
            'projectSource',
            $fileSystemMock,
        );

        $this->expectExceptionObject(
            new InvalidCoverage(
                'Could not find the XML coverage file "/path/to/coverage-dir/zeroLevel.php.xml" listed in "/path/to/index.xml". Make sure the coverage used is up to date',
            ),
        );

        $provider->provideFileInfo();
    }

    public function test_it_errors_when_the_source_file_could_not_be_found(): void
    {
        $fileSystemMock = $this->createMock(FileSystem::class);
        $fileSystemMock
            ->expects($this->once())
            ->method('isReadableFile')
            ->with('/path/to/project/var/coverage-xml/src/zeroLevel.php.xml')
            ->willReturn(true);
        $fileSystemMock
            ->expects($this->once())
            ->method('readFile')
            ->with('/path/to/project/var/coverage-xml/src/zeroLevel.php.xml')
            ->willReturn(
                <<<'XML'
                    <?xml version="1.0"?>
                    <phpunit xmlns="https://schema.phpunit.de/coverage/1.0">
                      <file name="Calculator.php" path="/Covered/Zero" hash="5166fd6f45f4b26afab9eaa7968e0c023bc35461">
                      </file>
                    </phpunit>
                    XML,
            );
        $fileSystemMock
            ->expects($this->once())
            ->method('realPath')
            ->with('/path/to/project/src/Covered/Zero/Calculator.php')
            ->willThrowException(new IOException(''));

        $provider = new SourceFileInfoProvider(
            '/path/to/project/var/coverage-xml/index.xml',
            '/path/to/project/var/coverage-xml',
            'src/zeroLevel.php.xml',
            '/path/to/project/src',
            $fileSystemMock,
        );

        $this->expectExceptionObject(
            new InvalidCoverage(
                'Could not find the source file "/path/to/project/src/Covered/Zero/Calculator.php" referred by "/path/to/project/var/coverage-xml/src/zeroLevel.php.xml". Make sure the coverage used is up to date',
            ),
        );

        $provider->provideFileInfo();
    }

    public function test_it_errors_when_the_xml_file_contains_invalid_xml(): void
    {
        $fileSystemMock = $this->createMock(FileSystem::class);
        $fileSystemMock
            ->expects($this->once())
            ->method('isReadableFile')
            ->willReturn(true);
        $fileSystemMock
            ->expects($this->once())
            ->method('readFile')
            ->willReturn('');

        $provider = new SourceFileInfoProvider(
            '/path/to/index.xml',
            '/path/to/coverage-dir',
            'zeroLevel.php.xml',
            'projectSource',
            $fileSystemMock,
        );

        // TODO: this is not ideal...
        $this->expectException(ValueError::class);

        $provider->provideFileInfo();
    }

    public static function fileFixturesProvider(): iterable
    {
        foreach (XmlCoverageFixtures::provideAllFixtures() as $fixture) {
            yield [
                '/path/to/index.xml',
                Path::canonicalize($fixture->coverageDir),
                $fixture->relativeCoverageFilePath,
                Path::canonicalize($fixture->projectSource),
                $fixture->sourceFilePath,
            ];
        }

        yield [
            '/path/to/index.xml',
            Path::canonicalize(__DIR__ . '/../../../Fixtures/Files/phpunit/coverage/coverage-xml'),
            'FirstLevel/firstLevelNotInIndex.php.xml',
            '/path/to/src',
            '/path/to/src/FirstLevel/firstLevelNotIndexed.php',
        ];
    }
}
