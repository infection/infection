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

namespace Infection\Tests\TestFramework\Coverage;

use Infection\TestFramework\Coverage\CoveredFileData;
use Infection\TestFramework\Coverage\CoveredFileDataFactory;
use Infection\TestFramework\Coverage\CoveredFileDataProvider;
use Infection\TestFramework\Coverage\CoveredFileNameFilter;
use Infection\TestFramework\Coverage\JUnit\JUnitTestExecutionInfoAdder;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use Symfony\Component\Finder\SplFileInfo;
use Traversable;

/**
 * @covers \Infection\TestFramework\Coverage\CoveredFileDataFactory
 */
final class CoveredFileDataFactoryTest extends TestCase
{
    public function test_it_provides_files(): void
    {
        $canary = [1, 2, 3];

        $providedFiles = $this->factoryProvidesFiles(
            $canary,
            [$this->createMock(SplFileInfo::class)],
            true
        );

        $this->assertSame($canary, $providedFiles);
        $this->assertCount(3, $canary);
    }

    public function test_it_adds_source_files_to_provided_files(): void
    {
        $inputFileNames = [
            'src/Foo.php',
            'src/Bar.php',
        ];

        $expectedFileNames = [
            'src/Foo.php',
            'src/Bar.php',
            'src/Baz.php',
            'src/Test/Foo.php',
        ];

        $providedFiles = $this->factoryProvidesFiles(
            take($inputFileNames)->map(function (string $filename) {
                return $this->createCoveredFileDataMock($filename);
            }),
            take($expectedFileNames)->map(function (string $filename) {
                return $this->createSplFileInfoMock($filename);
            }),
            false
        );

        $actualFileNames = take($providedFiles)->map(static function (CoveredFileData $data) {
            return $data->getSplFileInfo()->getRealPath();
        })->toArray();

        $this->assertSame($expectedFileNames, $actualFileNames);
    }

    public function test_it_provides_added_source_files_with_no_coverage(): void
    {
        $providedFiles = $this->factoryProvidesFiles(
            [],
            [$this->createSplFileInfoMock('src/Foo.php')],
            false
        );

        if ($providedFiles instanceof Traversable) {
            $providedFiles = iterator_to_array($providedFiles);
        }

        /** @var CoveredFileData $fileWithoutCoverage */
        $fileWithoutCoverage = $providedFiles[0];

        $this->assertFalse($fileWithoutCoverage->hasTests());
    }

    public function test_it_ignores_source_files_when_only_covered(): void
    {
        $expectedFileNames = [
            'src/Foo.php',
            'src/Bar.php',
        ];

        $inputFileNames = [
            'src/Foo.php',
            'src/Bar.php',
            'src/Baz.php',
            'src/Test/Foo.php',
        ];

        $providedFiles = $this->factoryProvidesFiles(
            take($expectedFileNames)->map(function (string $filename) {
                return $this->createCoveredFileDataMock($filename);
            }),
            take($inputFileNames)->map(function (string $filename) {
                return $this->createSplFileInfoMock($filename);
            }),
            true
        );

        $actualFileNames = take($providedFiles)->map(static function (CoveredFileData $data) {
            return $data->getSplFileInfo()->getRealPath();
        })->toArray();

        $this->assertSame($expectedFileNames, $actualFileNames);
    }

    private function factoryProvidesFiles(
        iterable $canary,
        iterable $sourceFiles,
        bool $onlyCovered
    ): iterable {
        $coveredFileDataProvider = $this->createMock(CoveredFileDataProvider::class);
        $coveredFileDataProvider
            ->expects($this->once())
            ->method('provideFiles')
            ->willReturn($canary)
        ;

        $filter = $this->createMock(CoveredFileNameFilter::class);
        $filter
            ->expects($this->once())
            ->method('filter')
            ->with($canary)
            ->willReturn($canary)
        ;

        $testFileDataAdder = $this->createMock(JUnitTestExecutionInfoAdder::class);
        $testFileDataAdder
            ->expects($this->once())
            ->method('addTestExecutionInfo')
            ->with($canary)
            ->willReturn($canary)
        ;

        $factory = new CoveredFileDataFactory(
            $coveredFileDataProvider,
            $testFileDataAdder,
            $filter,
            $sourceFiles,
            $onlyCovered
        );

        return $factory->provideFiles();
    }

    private function createSplFileInfoMock(string $filename): SplFileInfo
    {
        $splFileInfoMock = $this->createMock(SplFileInfo::class);
        $splFileInfoMock
            ->method('getRealPath')
            ->willReturn($filename)
        ;

        return $splFileInfoMock;
    }

    private function createCoveredFileDataMock(string $filename): CoveredFileData
    {
        $splFileInfoMock = $this->createSplFileInfoMock($filename);

        $codeCoverageMock = $this->createMock(CoveredFileData::class);
        $codeCoverageMock
            ->method('getSplFileInfo')
            ->willReturn($splFileInfoMock)
        ;

        return $codeCoverageMock;
    }
}
