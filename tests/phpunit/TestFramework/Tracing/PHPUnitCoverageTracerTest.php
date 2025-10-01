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
use Infection\FileSystem\Filesystem;
use Infection\FileSystem\SplFileInfoFactory;
use Infection\TestFramework\Coverage\TestLocations;
use Infection\TestFramework\Coverage\Trace;
use Infection\TestFramework\NewCoverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\NewCoverage\PHPUnitXml\Index\IndexReportLocator;
use Infection\TestFramework\NewCoverage\PHPUnitXml\PHPUnitXmlProvider;
use Infection\TestFramework\Tracing\PHPUnitCoverageTracer;
use Infection\TestFramework\Tracing\SyntheticTrace;
use Infection\Tests\TestFramework\Tracing\Fixtures\DemoCounterServiceTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function sprintf;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\SplFileInfo;

#[CoversClass(PHPUnitCoverageTracer::class)]
final class PHPUnitCoverageTracerTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/Fixtures';

    private PHPUnitCoverageTracer $tracer;

    protected function setUp(): void
    {
        $filesystem = new Filesystem();

        $this->tracer = new PHPUnitCoverageTracer(
            new PHPUnitXmlProvider(
                indexReportLocator: IndexReportLocator::create(
                    $filesystem,
                    self::FIXTURE_DIR . '/phpunit',
                ),
                jUnitReportLocator: JUnitReportLocator::create(
                    $filesystem,
                    self::FIXTURE_DIR . '/phpunit',
                ),
            ),
        );
    }

    #[DataProvider('traceProvider')]
    public function test_it_can_create_a_trace(
        SplFileInfo $fileInfo,
        Trace $expected,
    ): void {
        $actual = $this->tracer->trace($fileInfo);

        TraceAssertion::assertEquals($expected, $actual);
    }

    public static function traceProvider(): iterable
    {
        $splFileInfo = SplFileInfoFactory::fromPath(
            self::FIXTURE_DIR . '/DemoCounterService.php',
            self::FIXTURE_DIR,
        );

        $testLocations = [
            new TestLocation(
                sprintf(
                    '%s::test_set_step_with_default_resets_to_one',
                    DemoCounterServiceTest::class,
                ),
                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                0.036343,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_multiple_counts_increment_correctly',
                    DemoCounterServiceTest::class,
                ),
                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                0.036343,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_custom_step_with_multiple_counts',
                    DemoCounterServiceTest::class,
                ),
                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                0.036343,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_set_step_changes_increment_amount',
                    DemoCounterServiceTest::class,
                ),
                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                0.036343,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_start_count_affects_subsequent_counts',
                    DemoCounterServiceTest::class,
                ),
                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                0.036343,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_start_count_with_default_sets_to_zero',
                    DemoCounterServiceTest::class,
                ),
                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                0.036343,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_zero_step_keeps_counter_unchanged',
                    DemoCounterServiceTest::class,
                ),
                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                0.036343,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_negative_step_decreases_counter',
                    DemoCounterServiceTest::class,
                ),
                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                0.036343,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_count_increments_by_step_and_returns_new_value',
                    DemoCounterServiceTest::class,
                ),
                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                0.036343,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_complex_scenario',
                    DemoCounterServiceTest::class,
                ),
                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                0.036343,
            ),
        ];

        yield [
            $splFileInfo,
            new SyntheticTrace(
                sourceFileInfo: $splFileInfo,
                realPath: Path::canonicalize(self::FIXTURE_DIR . '/DemoCounterService.php'),
                relativePathname: 'DemoCounterService.php',
                hasTest: true,
                tests: new TestLocations(
                    [
                        14 => $testLocations,
                        15 => $testLocations,
                        17 => $testLocations,
                        22 => [
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_affects_subsequent_counts',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_with_default_sets_to_zero',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_zero_step_keeps_counter_unchanged',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_negative_step_decreases_counter',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_sets_initial_value',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_complex_scenario',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                        ],
                        27 => [
                            new TestLocation(
                                sprintf(
                                    '%s::test_set_step_with_default_resets_to_one',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_custom_step_with_multiple_counts',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_set_step_changes_increment_amount',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_zero_step_keeps_counter_unchanged',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_negative_step_decreases_counter',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_complex_scenario',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                        ],
                        32 => [
                            new TestLocation(
                                sprintf(
                                    '%s::test_multiple_counts_increment_correctly',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_custom_step_with_multiple_counts',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_set_step_changes_increment_amount',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_affects_subsequent_counts',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_with_default_sets_to_zero',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_initial_counter_is_zero',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_zero_step_keeps_counter_unchanged',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_negative_step_decreases_counter',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_count_increments_by_step_and_returns_new_value',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_sets_initial_value',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_complex_scenario',
                                    DemoCounterServiceTest::class,
                                ),
                                '/Users/tfidry/Project/Humbug/infection/tests/phpunit/TestFramework/Tracing/Fixtures/DemoCounterServiceTest.php',
                                0.036343,
                            ),
                        ],
                    ],
                    [],
                ),
            ),
        ];
    }
}
