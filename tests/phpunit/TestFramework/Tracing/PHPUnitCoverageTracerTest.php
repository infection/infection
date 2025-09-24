<?php

namespace Infection\Tests\TestFramework\Tracing;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\FileSystem\Filesystem;
use Infection\FileSystem\SplFileInfoFactory;
use Infection\TestFramework\Coverage\TestLocations;
use Infection\TestFramework\Coverage\Trace;
use Infection\TestFramework\NewCoverage\JUnit\JUnitReportLocator;
use Infection\TestFramework\NewCoverage\Locator\HardcodedLocator;
use Infection\TestFramework\NewCoverage\PHPUnitXml\PHPUnitXmlProvider;
use Infection\TestFramework\Tracing\PHPUnitCoverageTracer;
use Infection\TestFramework\NewCoverage\PHPUnitXml\Index\IndexReportLocator;
use Infection\TestFramework\Tracing\SyntheticTrace;
use Infection\Tests\TestFramework\Tracing\Fixtures\DemoCounterServiceTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\SplFileInfo;
use function array_fill_keys;
use function sprintf;

#[CoversClass(PHPUnitCoverageTracer::class)]
final class PHPUnitCoverageTracerTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__.'/Fixtures';

    private PHPUnitCoverageTracer $tracer;

    protected function setUp(): void
    {
        $filesystem = new Filesystem();

        $this->tracer = new PHPUnitCoverageTracer(
            new PHPUnitXmlProvider(
                indexReportLocator: IndexReportLocator::create(
                    $filesystem,
                    self::FIXTURE_DIR.'/phpunit',
                ),
                jUnitReportLocator: JUnitReportLocator::create(
                    $filesystem,
                    self::FIXTURE_DIR.'/phpunit',
                ),
            ),
        );
    }

    #[DataProvider('traceProvider')]
    public function test_it_can_create_a_trace(
        SplFileInfo $fileInfo,
        Trace $expected,
    ): void
    {
        $actual = $this->tracer->trace($fileInfo);

        TraceAssertion::assertEquals($expected, $actual);
    }

    public static function traceProvider(): iterable
    {
        $splFileInfo = SplFileInfoFactory::fromPath(
            self::FIXTURE_DIR.'/DemoCounterService.php',
            self::FIXTURE_DIR,
        );

        $testLocations = [
            new TestLocation(
                sprintf(
                    '%s::test_set_step_with_default_resets_to_one',
                    DemoCounterServiceTest::class,
                ),
                null,
                null,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_multiple_counts_increment_correctly',
                    DemoCounterServiceTest::class,
                ),
                null,
                null,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_custom_step_with_multiple_counts',
                    DemoCounterServiceTest::class,
                ),
                null,
                null,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_set_step_changes_increment_amount',
                    DemoCounterServiceTest::class,
                ),
                null,
                null,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_start_count_affects_subsequent_counts',
                    DemoCounterServiceTest::class,
                ),
                null,
                null,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_start_count_with_default_sets_to_zero',
                    DemoCounterServiceTest::class,
                ),
                null,
                null,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_zero_step_keeps_counter_unchanged',
                    DemoCounterServiceTest::class,
                ),
                null,
                null,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_negative_step_decreases_counter',
                    DemoCounterServiceTest::class,
                ),
                null,
                null,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_count_increments_by_step_and_returns_new_value',
                    DemoCounterServiceTest::class,
                ),
                null,
                null,
            ),
            new TestLocation(
                sprintf(
                    '%s::test_complex_scenario',
                    DemoCounterServiceTest::class,
                ),
                null,
                null,
            ),
        ];
        yield [
            $splFileInfo,
            new SyntheticTrace(
                sourceFileInfo: $splFileInfo,
                realPath: Path::canonicalize(self::FIXTURE_DIR.'/DemoCounterService.php'),
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
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_with_default_sets_to_zero',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_zero_step_keeps_counter_unchanged',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_negative_step_decreases_counter',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_sets_initial_value',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_complex_scenario',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                        ],
                        27 => [
                            new TestLocation(
                                sprintf(
                                    '%s::test_set_step_with_default_resets_to_one',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_custom_step_with_multiple_counts',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_set_step_changes_increment_amount',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_zero_step_keeps_counter_unchanged',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_negative_step_decreases_counter',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_complex_scenario',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                        ],
                        32 => [
                            new TestLocation(
                                sprintf(
                                    '%s::test_multiple_counts_increment_correctly',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_custom_step_with_multiple_counts',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_set_step_changes_increment_amount',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_affects_subsequent_counts',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_with_default_sets_to_zero',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_initial_counter_is_zero',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_zero_step_keeps_counter_unchanged',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_negative_step_decreases_counter',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_count_increments_by_step_and_returns_new_value',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_start_count_sets_initial_value',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                            new TestLocation(
                                sprintf(
                                    '%s::test_complex_scenario',
                                    DemoCounterServiceTest::class,
                                ),
                                null,
                                null,
                            ),
                        ],
                    ],
                    [],
                ),
            ),
        ];
    }
}
