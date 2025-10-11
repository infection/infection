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

namespace Infection\Tests\TestFramework\Tracing;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\FileSystem\SourceFileFilter;
use Infection\TestFramework\Coverage\BufferedSourceFileFilter;
use Infection\TestFramework\Coverage\CoveredTraceProvider;
use Infection\TestFramework\Coverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\Coverage\JUnit\JUnitTestExecutionInfoAdder;
use Infection\TestFramework\Coverage\JUnit\JUnitTestFileDataProvider;
use Infection\TestFramework\Coverage\JUnit\MemoizedTestFileDataProvider;
use Infection\TestFramework\Coverage\SourceMethodLineRange;
use Infection\TestFramework\Coverage\TestLocations;
use Infection\TestFramework\Coverage\Trace;
use Infection\TestFramework\Coverage\TraceProvider;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocator;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageParser;
use Infection\TestFramework\Coverage\XmlReport\PhpUnitXmlCoverageTraceProvider;
use Infection\TestFramework\Coverage\XmlReport\XmlCoverageParser;
use Infection\TestFramework\Tracing\SyntheticTrace;
use Infection\Tests\TestFramework\Tracing\Fixtures\tests\DemoCounterServiceTest;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function sprintf;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

#[CoversNothing]
final class PHPUnitCoverageTracerTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/Fixtures';

    private TraceProvider $provider;

    protected function setUp(): void
    {
        $coveragePath = self::FIXTURE_DIR . '/phpunit';

        $testFrameworkAdapterStub = $this->createStub(TestFrameworkAdapter::class);
        $testFrameworkAdapterStub
            ->method('hasJUnitReport')
            ->willReturn(true);

        $this->provider = new CoveredTraceProvider(
            new PhpUnitXmlCoverageTraceProvider(
                new IndexXmlCoverageLocator($coveragePath),
                new IndexXmlCoverageParser(isForGitDiffLines: false),
                new XmlCoverageParser(),
            ),
            new JUnitTestExecutionInfoAdder(
                $testFrameworkAdapterStub,
                new MemoizedTestFileDataProvider(
                    new JUnitTestFileDataProvider(
                        new JUnitReportLocator(
                            coveragePath: $coveragePath,
                            defaultJUnitPath: 'junit.xml',
                        ),
                    ),
                ),
            ),
            new BufferedSourceFileFilter(
                new SourceFileFilter(
                    filter: '',
                    excludeDirectories: [],
                ),
                Finder::create()
                    ->files()
                    ->depth(0)
                    ->in(self::FIXTURE_DIR . '/src'),
            ),
        );
    }

    #[DataProvider('traceProvider')]
    public function test_it_can_create_a_trace(
        SplFileInfo $fileInfo,
        Trace $expected,
    ): void {
        $actual = 'No Trace found.';

        foreach ($this->provider->provideTraces() as $trace) {
            if ($trace->getSourceFileInfo()->getPathname() === $fileInfo->getPathname()) {
                $actual = $trace;

                break;
            }
        }

        TraceAssertion::assertEquals($expected, $actual);
    }

    public static function traceProvider(): iterable
    {
        $canonicalDemoCounterServicePathname = Path::canonicalize(self::FIXTURE_DIR . '/src/DemoCounterService.php');

        $splFileInfo = new SplFileInfo(
            file: self::FIXTURE_DIR . '/src/DemoCounterService.php',
            relativePath: '/',
            relativePathname: $canonicalDemoCounterServicePathname,
        );

        $testFilePath = Path::canonicalize(self::FIXTURE_DIR . '/tests/DemoCounterServiceTest.php');

        $testLocations = [
            new TestLocation(
                sprintf(
                    '%s::test_set_step_with_default_resets_to_one',
                    DemoCounterServiceTest::class,
                ),
                $testFilePath,
                0.021691,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_multiple_counts_increment_correctly',
                    DemoCounterServiceTest::class,
                ),
                $testFilePath,
                0.021691,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_custom_step_with_multiple_counts',
                    DemoCounterServiceTest::class,
                ),
                $testFilePath,
                0.021691,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_set_step_changes_increment_amount',
                    DemoCounterServiceTest::class,
                ),
                $testFilePath,
                0.021691,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_start_count_affects_subsequent_counts',
                    DemoCounterServiceTest::class,
                ),
                $testFilePath,
                0.021691,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_start_count_with_default_sets_to_zero',
                    DemoCounterServiceTest::class,
                ),
                $testFilePath,
                0.021691,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_zero_step_keeps_counter_unchanged',
                    DemoCounterServiceTest::class,
                ),
                $testFilePath,
                0.021691,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_negative_step_decreases_counter',
                    DemoCounterServiceTest::class,
                ),
                $testFilePath,
                0.021691,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_count_increments_by_step_and_returns_new_value',
                    DemoCounterServiceTest::class,
                ),
                $testFilePath,
                0.021691,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_complex_scenario',
                    DemoCounterServiceTest::class,
                ),
                $testFilePath,
                0.021691,
            ),
        ];

        yield [
            $splFileInfo,
            new SyntheticTrace(
                sourceFileInfo: $splFileInfo,
                realPath: $canonicalDemoCounterServicePathname,
                relativePathname: $canonicalDemoCounterServicePathname,
                hasTest: true,
                tests: new TestLocations(
                    [
                        46 => $testLocations,
                        47 => $testLocations,
                        49 => $testLocations,
                        54 => [
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_affects_subsequent_counts',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_with_default_sets_to_zero',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_zero_step_keeps_counter_unchanged',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_negative_step_decreases_counter',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_sets_initial_value',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_complex_scenario',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                        ],
                        59 => [
                            new TestLocation(
                                sprintf(
                                    '%s::test_set_step_with_default_resets_to_one',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_custom_step_with_multiple_counts',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_set_step_changes_increment_amount',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_zero_step_keeps_counter_unchanged',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_negative_step_decreases_counter',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_complex_scenario',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                        ],
                        64 => [
                            new TestLocation(
                                sprintf(
                                    '%s::test_multiple_counts_increment_correctly',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_custom_step_with_multiple_counts',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_set_step_changes_increment_amount',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_affects_subsequent_counts',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_with_default_sets_to_zero',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_initial_counter_is_zero',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_zero_step_keeps_counter_unchanged',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_negative_step_decreases_counter',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_count_increments_by_step_and_returns_new_value',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_sets_initial_value',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_complex_scenario',
                                    DemoCounterServiceTest::class,
                                ),
                                $testFilePath,
                                0.021691,
                            ),
                        ],
                    ],
                    [
                        'count' => new SourceMethodLineRange(44, 50),
                        'startCount' => new SourceMethodLineRange(52, 55),
                        'setStep' => new SourceMethodLineRange(57, 60),
                        'get' => new SourceMethodLineRange(62, 65),
                    ],
                ),
            ),
        ];
    }
}
