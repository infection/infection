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

use Infection\AbstractTestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\Coverage\CoverageDoesNotExistException;
use Infection\TestFramework\Coverage\CoverageFileData;
use Infection\TestFramework\Coverage\MethodLocationData;
use Infection\TestFramework\Coverage\XmlReport\PhpUnitXmlCoveredFileDataFactory;
use Infection\TestFramework\Coverage\XmlReport\XMLLineCodeCoverageProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

final class XMLLineCodeCoverageProviderTest extends TestCase
{
    public function test_it_determines_file_is_not_covered_for_unknown_path(): void
    {
        $filePath = '/path/to/unknown-file';

        $codeCoverageDataProvider = $this->createCodeCoverageDataProvider();
        $codeCoverageData = $codeCoverageDataProvider->createFor($this->createSplFileInfo($filePath));

        $this->assertFalse($codeCoverageData->hasTests());
    }

    public function test_it_determines_file_is_covered(): void
    {
        $filePath = '/path/to/acme/Foo.php';

        $codeCoverageDataProvider = $this->createCodeCoverageDataProvider();
        $codeCoverageData = $codeCoverageDataProvider->createFor($this->createSplFileInfo($filePath));

        $this->assertTrue($codeCoverageData->hasTests());
    }

    public function test_it_throws_an_exception_when_no_coverage_found(): void
    {
        $coverageFactoryMock = $this->createMock(PhpUnitXmlCoveredFileDataFactory::class);
        $coverageFactoryMock
            ->expects($this->once())
            ->method('createCoverage')
            ->willThrowException($exception = new CoverageDoesNotExistException())
        ;

        $splFileInfoMock = $this->createMock(SplFileInfo::class);
        $splFileInfoMock
            ->expects($this->never())
            ->method('getRealPath')
        ;

        $codeCoverageDataProvider = new XMLLineCodeCoverageProvider($coverageFactoryMock);

        try {
            $codeCoverageDataProvider->createFor($splFileInfoMock);

            $this->fail();
        } catch (CoverageDoesNotExistException $caughtException) {
            $this->assertSame($exception, $caughtException);
        }
    }

    private function getParsedCodeCoverageData(): array
    {
        return [
            '/path/to/acme/Foo.php' => new CoverageFileData(
                [
                    26 => [
                        CoverageLineData::with(
                            'Infection\\Acme\\FooTest::test_it_can_do_0',
                            '/path/to/acme/FooTest.php',
                            0.123
                        ),
                        CoverageLineData::with(
                            'Infection\\Acme\\FooTest::test_it_can_do_1',
                            '/path/to/acme/FooTest.php',
                            0.456
                        ),
                    ],
                    30 => [
                        CoverageLineData::with(
                            'Infection\\Acme\\FooTest::test_it_can_do_0',
                            '/path/to/acme/FooTest.php',
                            0.123
                        ),
                        CoverageLineData::with(
                            'Infection\\Acme\\FooTest::test_it_can_do_1',
                            '/path/to/acme/FooTest.php',
                            0.456
                        ),
                    ],
                    31 => [
                        CoverageLineData::with(
                            'Infection\\Acme\\FooTest::test_it_can_do_1',
                            '/path/to/acme/FooTest.php',
                            0.456
                        ),
                    ],
                    34 => [
                        CoverageLineData::with(
                            'Infection\\Acme\\FooTest::test_it_can_do_0',
                            '/path/to/acme/FooTest.php',
                            0.123
                        ),
                    ],
                ],
                [
                    'do0' => new MethodLocationData(19, 22),
                    'do1' => new MethodLocationData(24, 35),
                    'doSomethingUncovered' => new MethodLocationData(3, 5),
                ]
            ),
        ];
    }

    private function createSplFileInfo(string $filePath): SplFileInfo
    {
        $splFileInfoMock = $this->createMock(SplFileInfo::class);
        $splFileInfoMock
            ->expects($this->once())
            ->method('getRealPath')
            ->willReturn(false)
        ;

        $splFileInfoMock
            ->expects($this->once())
            ->method('getPathname')
            ->willReturn($filePath)
        ;

        return $splFileInfoMock;
    }

    private function createCodeCoverageDataProvider(): XMLLineCodeCoverageProvider
    {
        $coverageFactoryMock = $this->createMock(PhpUnitXmlCoveredFileDataFactory::class);
        $coverageFactoryMock
            ->expects($this->once())
            ->method('createCoverage')
            ->willReturn($this->getParsedCodeCoverageData())
        ;

        return new XMLLineCodeCoverageProvider($coverageFactoryMock);
    }
}
