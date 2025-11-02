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

namespace Infection\Tests\TestFramework\NewCoverage\PHPUnitXml\File;

use Infection\TestFramework\NewCoverage\PHPUnitXml\File\FileReport;
use Infection\TestFramework\NewCoverage\PHPUnitXml\File\LineCoverage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;

#[CoversClass(FileReport::class)]
final class FileReportTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/Fixtures';

    /**
     * @param non-empty-list<LineCoverage> $expected
     */
    #[DataProvider('coverageProvider')]
    public function test_it_can_tell_if_a_source_file_has_tests(
        string $xmlPathname,
        array $expected,
    ): void {
        $report = new FileReport(
            Path::canonicalize($xmlPathname),
        );

        $actual = $report->getCoverage();

        $this->assertEquals($expected, $actual);
    }

    public static function coverageProvider(): iterable
    {
        yield 'multiple lines covered by a single test each' => [
            self::FIXTURE_DIR . '/MemoizedCiDetector.php.xml',
            [
                new LineCoverage(
                    56,
                    ['Infection\Tests\CI\MemoizedCiDetectorTest::test_it_runs_the_detection_only_once'],
                ),
                new LineCoverage(
                    57,
                    ['Infection\Tests\CI\MemoizedCiDetectorTest::test_it_runs_the_detection_only_once'],
                ),
                new LineCoverage(
                    60,
                    ['Infection\Tests\CI\MemoizedCiDetectorTest::test_it_runs_the_detection_only_once'],
                ),
            ],
        ];

        yield 'multiple lines covered by multiple tests each' => [
            self::FIXTURE_DIR . '/Str.php.xml',
            [
                new LineCoverage(
                    56,
                    [
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#string with leading & trailing line returns',
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#string with trailing line returns',
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#string with leading, trailing & in-between line returns',
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#empty',
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#string with leading, trailing & in-between line returns & dirty empty strings',
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#string without line return',
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#string with untrimmed spaces',
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#string with leading line returns',
                    ],
                ),
                new LineCoverage(
                    57,
                    [
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#string with leading & trailing line returns',
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#string with trailing line returns',
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#string with leading, trailing & in-between line returns',
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#empty',
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#string with leading, trailing & in-between line returns & dirty empty strings',
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#string without line return',
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#string with untrimmed spaces',
                        'Infection\Tests\StrTest::test_it_can_trim_string_of_line_returns#string with leading line returns',
                    ],
                ),
            ],
        ];
    }

    // This is because we use to construct a Trace, hence we what should be
    // memoized is the end result. Otherwise, we are just bloating the memory
    // unnecessarily.
    public function test_the_information_is_not_memoized(): void
    {
        $report = new FileReport(self::FIXTURE_DIR . '/MemoizedCiDetector.php.xml');

        $coverage1 = $report->getCoverage();
        $coverage2 = $report->getCoverage();

        $this->assertEquals($coverage1, $coverage2);
        $this->assertNotSame($coverage1, $coverage2);
    }
}
