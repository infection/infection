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

use Infection\AbstractTestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\Coverage\CoverageReport;
use Infection\TestFramework\Coverage\MethodLocationData;
use Infection\TestFramework\Coverage\NodeLineRangeData;
use Infection\TestFramework\Coverage\SourceFileData;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @covers \Infection\TestFramework\Coverage\SourceFileData
 */
final class SourceFileDataTest extends TestCase
{
    public function test_returns_file_info(): void
    {
        $splFileInfoMock = $this->createMock(SplFileInfo::class);

        $coveredFileData = new SourceFileData($splFileInfoMock, []);

        $actual = $coveredFileData->getSplFileInfo();

        $this->assertSame($splFileInfoMock, $actual);
    }

    public function test_it_can_return_real_path(): void
    {
        $expected = 'Foo.php';

        $splFileInfoMock = $this->createMock(SplFileInfo::class);
        $splFileInfoMock
            ->method('getRealPath')
            ->willReturn($expected);

        $coveredFileData = new SourceFileData($splFileInfoMock, []);

        $actual = $coveredFileData->getRealPath();

        $this->assertSame($expected, $actual);
    }

    public function test_it_can_retreive_file_data(): void
    {
        $splFileInfoMock = $this->createMock(SplFileInfo::class);
        $coverageFileData = new CoverageReport();

        $coveredFileData = new SourceFileData($splFileInfoMock, [$coverageFileData, null]);

        $actual = $coveredFileData->retrieveCoverageReport();
        $this->assertSame($coverageFileData, $actual);

        // From cache
        $actual = $coveredFileData->retrieveCoverageReport();
        $this->assertSame($coverageFileData, $actual);
    }

    public function test_it_can_detect_coverage_data_without_tests(): void
    {
        $splFileInfoMock = $this->createMock(SplFileInfo::class);

        $coverageFileData = new CoverageReport();

        $coveredFileData = new SourceFileData($splFileInfoMock, [$coverageFileData]);

        $this->assertFalse($coveredFileData->hasTests());
    }

    public function test_it_proxies_call_file_code_coverage(): void
    {
        $splFileInfoMock = $this->createMock(SplFileInfo::class);

        $coverageFileData = new CoverageReport(
            [
                21 => [
                    CoverageLineData::withTestMethod('Acme\FooTest::test_it_can_be_instantiated'),
                ],
            ],
            [
                '__construct' => new MethodLocationData(
                    19,
                    22
                ),
            ]
        );

        $coveredFileData = new SourceFileData($splFileInfoMock, [$coverageFileData]);

        $this->assertTrue($coveredFileData->hasTests());

        $this->assertCount(0, $coveredFileData->getAllTestsForMutation(
            new NodeLineRangeData(1, 1),
            false
        ));

        $this->assertCount(1, $coveredFileData->getAllTestsForMutation(
            new NodeLineRangeData(20, 21),
            false
        ));

        // This iterator_to_array is due to bug in our version of PHPUnit
        $this->assertCount(0, iterator_to_array($coveredFileData->getAllTestsForMutation(
            new NodeLineRangeData(1, 1),
            true
        )));

        $this->assertCount(1, iterator_to_array($coveredFileData->getAllTestsForMutation(
            new NodeLineRangeData(19, 22),
            true
        )));
    }
}
