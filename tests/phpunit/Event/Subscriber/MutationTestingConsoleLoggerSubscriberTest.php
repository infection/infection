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

namespace Infection\Tests\Event\Subscriber;

use Infection\Event\EventDispatcher\SyncEventDispatcher;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantProcessWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasStarted;
use Infection\Event\Subscriber\MutationTestingConsoleLoggerSubscriber;
use Infection\Framework\Str;
use Infection\Logger\MutationAnalysis\MutationAnalysisLogger;
use Infection\Metrics\MetricsCalculator;
use Infection\Mutant\MutantExecutionResult;
use Infection\Process\Runner\ProcessRunner;
use Infection\Reporter\Reporter;
use Infection\Tests\Reporter\NullReporter;
use const PHP_EOL;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function Safe\fopen;
use function Safe\rewind;
use function Safe\stream_get_contents;
use function str_replace;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

#[Group('integration')]
#[CoversClass(MutationTestingConsoleLoggerSubscriber::class)]
final class MutationTestingConsoleLoggerSubscriberTest extends TestCase
{
    private MockObject&MutationAnalysisLogger $logger;

    private MockObject&MetricsCalculator $metricsCalculator;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(MutationAnalysisLogger::class);
        $this->metricsCalculator = $this->createMock(MetricsCalculator::class);
    }

    public function test_it_reacts_on_mutation_testing_started(): void
    {
        $this->logger
            ->expects($this->once())
            ->method('startAnalysis');

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $this->createStub(OutputInterface::class),
            $this->logger,
            $this->metricsCalculator,
            new NullReporter(),
            new NullReporter(),
            withUncovered: true,
        ));

        $processRunner = $this->createStub(ProcessRunner::class);

        $dispatcher->dispatch(new MutationTestingWasStarted(1, $processRunner));
    }

    public function test_it_reacts_on_mutation_process_finished(): void
    {
        $this->metricsCalculator
            ->expects($this->never())
            ->method('collect');

        $this->logger
            ->expects($this->once())
            ->method('finishEvaluation');

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $this->createStub(OutputInterface::class),
            $this->logger,
            $this->metricsCalculator,
            new NullReporter(),
            new NullReporter(),
            withUncovered: true,
        ));

        $dispatcher->dispatch(
            new MutantProcessWasFinished(
                $this->createStub(MutantExecutionResult::class),
            ),
        );
    }

    public function test_it_reacts_on_mutation_testing_finished(): void
    {
        $this->logger
            ->expects($this->once())
            ->method('finishAnalysis');

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $this->createStub(OutputInterface::class),
            $this->logger,
            $this->metricsCalculator,
            new NullReporter(),
            new NullReporter(),
            withUncovered: true,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());
    }

    public function test_it_does_not_output_escaped_mutants_when_mutation_testing_is_finished_with_no_escaped_mutants(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        $this->metricsCalculator
            ->method('getKilledByTestsCount')
            ->willReturn(0);
        // less important metrics, only rendered when > 0
        $this->metricsCalculator
            ->method('getKilledByStaticAnalysisCount')
            ->willReturn(0);
        $this->metricsCalculator
            ->method('getIgnoredCount')
            ->willReturn(0);
        $this->metricsCalculator
            ->method('getNotTestedCount')
            ->willReturn(0);
        $this->metricsCalculator
            ->method('getEscapedCount')
            ->willReturn(0);
        $this->metricsCalculator
            ->method('getErrorCount')
            ->willReturn(0);
        $this->metricsCalculator
            ->method('getSyntaxErrorCount')
            ->willReturn(0);
        $this->metricsCalculator
            ->method('getTimedOutCount')
            ->willReturn(0);
        $this->metricsCalculator
            ->method('getSkippedCount')
            ->willReturn(0);

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $output,
            $this->logger,
            $this->metricsCalculator,
            new NullReporter(),
            new NullReporter(),
            withUncovered: true,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $this->assertStringContainsString(
            "\n\nMetrics:\n",
            $this->getDisplay($output),
        );

        $this->assertStringContainsString(
            "\n\n0 mutations were generated:",
            $this->getDisplay($output),
        );

        // contains
        $this->assertStringContainsString(
            '       0 mutants were killed by Test Framework',
            $this->getDisplay($output),
        );

        // not contains
        $this->assertStringNotContainsString(
            'mutants were caught by Static Analysis',
            $this->getDisplay($output),
        );
        $this->assertStringNotContainsString(
            'mutants were configured to be ignored',
            $this->getDisplay($output),
        );
        $this->assertStringNotContainsString(
            'mutants were not covered by tests',
            $this->getDisplay($output),
        );
        $this->assertStringNotContainsString(
            'covered mutants were not detected',
            $this->getDisplay($output),
        );
        $this->assertStringNotContainsString(
            'errors were encountered',
            $this->getDisplay($output),
        );
        $this->assertStringNotContainsString(
            'syntax errors were encountered',
            $this->getDisplay($output),
        );
        $this->assertStringNotContainsString(
            'time outs were encountered',
            $this->getDisplay($output),
        );
        $this->assertStringNotContainsString(
            'mutants required more time than configured',
            $this->getDisplay($output),
        );
    }

    #[DataProvider('metricsProvider')]
    public function test_it_show_the_metrics(MetricsScenario $scenario): void
    {
        $output = new BufferedOutput();

        $this->configureMetricsCalculatorMock($scenario);

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $output,
            $this->logger,
            $this->metricsCalculator,
            new NullReporter(),
            new NullReporter(),
            withUncovered: $scenario->withUncovered,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $actual = Str::toUnixLineEndings($output->fetch());

        $this->assertSame($scenario->expected, $actual);
    }

    public static function metricsProvider(): iterable
    {
        $emptyScenario = new MetricsScenario(
            withUncovered: false,
            timeoutsAsEscaped: false,
            killedByTestsCount: 0,
            killedByStaticAnalysisCount: 0,
            errorCount: 0,
            syntaxErrorCount: 0,
            skippedCount: 0,
            ignoredCount: 0,
            escapedCount: 0,
            timedOutCount: 0,
            notTestedCount: 0,
            totalMutantsCount: 0,
            mutationScoreIndicator: 0.,
            coverageRate: 0.,
            coveredCodeMutationScoreIndicator: 0.,
            expected: <<<'DISPLAY'


                0 mutations were generated:
                       0 mutants were killed by Test Framework

                Metrics:
                         Mutation Code Coverage: <low>0%</low>
                         Covered Code MSI: <low>0%</low>

                Please note that some mutants will inevitably be harmless (i.e. false positives).

                DISPLAY,
        );

        $completeScenario = new MetricsScenario(
            withUncovered: false,
            timeoutsAsEscaped: false,
            killedByTestsCount: 3,
            killedByStaticAnalysisCount: 2,
            errorCount: 1,
            syntaxErrorCount: 10,
            skippedCount: 2,
            ignoredCount: 1,
            escapedCount: 4,
            timedOutCount: 2,
            notTestedCount: 3,
            totalMutantsCount: 6,
            mutationScoreIndicator: 52.,
            coverageRate: 42.,
            coveredCodeMutationScoreIndicator: 74.,
            expected: <<<'DISPLAY'


                6 mutations were generated:
                       3 mutants were killed by Test Framework
                       2 mutants were caught by Static Analysis
                       1 mutants were configured to be ignored
                       3 mutants were not covered by tests
                       4 covered mutants were not detected
                       1 errors were encountered
                      10 syntax errors were encountered
                       2 time outs were encountered
                       2 mutants required more time than configured

                Metrics:
                         Mutation Code Coverage: <low>42%</low>
                         Covered Code MSI: <medium>74%</medium>

                Please note that some mutants will inevitably be harmless (i.e. false positives).

                DISPLAY,
        );

        yield 'no metrics' => $emptyScenario->build();

        yield 'no metrics with uncovered' => $emptyScenario
            ->withUncovered(true)
            ->withExpected(
                <<<'DISPLAY'


                    0 mutations were generated:
                           0 mutants were killed by Test Framework

                    Metrics:
                             Mutation Score Indicator (MSI): <low>0%</low>
                             Mutation Code Coverage: <low>0%</low>
                             Covered Code MSI: <low>0%</low>

                    Please note that some mutants will inevitably be harmless (i.e. false positives).

                    DISPLAY,
            )
            ->build();

        yield 'no metrics with timeouts as escaped' => $emptyScenario
            ->withTimeoutsAsEscaped(true)
            ->build();

        yield 'all metrics' => $completeScenario->build();

        yield 'all metrics with uncovered' => $completeScenario
            ->withUncovered(true)
            ->withExpected(
                <<<'DISPLAY'


                    6 mutations were generated:
                           3 mutants were killed by Test Framework
                           2 mutants were caught by Static Analysis
                           1 mutants were configured to be ignored
                           3 mutants were not covered by tests
                           4 covered mutants were not detected
                           1 errors were encountered
                          10 syntax errors were encountered
                           2 time outs were encountered
                           2 mutants required more time than configured

                    Metrics:
                             Mutation Score Indicator (MSI): <medium>52%</medium>
                             Mutation Code Coverage: <low>42%</low>
                             Covered Code MSI: <medium>74%</medium>

                    Please note that some mutants will inevitably be harmless (i.e. false positives).

                    DISPLAY,
            )
            ->build();

        yield 'all metrics with timeouts as escaped' => $completeScenario
            ->withTimeoutsAsEscaped(true)
            ->build();

        yield 'it marks all percentages as low if bellow the low threshold' => $emptyScenario
            ->withUncovered(true)
            ->withMutationScoreIndicator(49)
            ->withCoverageRate(49)
            ->withCoveredCodeMutationScoreIndicator(49)
            ->withExpected(
                <<<'DISPLAY'


                    0 mutations were generated:
                           0 mutants were killed by Test Framework

                    Metrics:
                             Mutation Score Indicator (MSI): <low>49%</low>
                             Mutation Code Coverage: <low>49%</low>
                             Covered Code MSI: <low>49%</low>

                    Please note that some mutants will inevitably be harmless (i.e. false positives).

                    DISPLAY,
            )
            ->build();

        yield 'it marks all percentages as meidum if above the low threshold' => $emptyScenario
            ->withUncovered(true)
            ->withMutationScoreIndicator(50)
            ->withCoverageRate(50)
            ->withCoveredCodeMutationScoreIndicator(50)
            ->withExpected(
                <<<'DISPLAY'


                    0 mutations were generated:
                           0 mutants were killed by Test Framework

                    Metrics:
                             Mutation Score Indicator (MSI): <medium>50%</medium>
                             Mutation Code Coverage: <medium>50%</medium>
                             Covered Code MSI: <medium>50%</medium>

                    Please note that some mutants will inevitably be harmless (i.e. false positives).

                    DISPLAY,
            )
            ->build();

        yield 'it marks all percentages as medium if bellow the high threshold' => $emptyScenario
            ->withUncovered(true)
            ->withMutationScoreIndicator(89)
            ->withCoverageRate(89)
            ->withCoveredCodeMutationScoreIndicator(89)
            ->withExpected(
                <<<'DISPLAY'


                    0 mutations were generated:
                           0 mutants were killed by Test Framework

                    Metrics:
                             Mutation Score Indicator (MSI): <medium>89%</medium>
                             Mutation Code Coverage: <medium>89%</medium>
                             Covered Code MSI: <medium>89%</medium>

                    Please note that some mutants will inevitably be harmless (i.e. false positives).

                    DISPLAY,
            )
            ->build();

        yield 'it marks all percentages as high if above the high threshold' => $emptyScenario
            ->withUncovered(true)
            ->withMutationScoreIndicator(90)
            ->withCoverageRate(90)
            ->withCoveredCodeMutationScoreIndicator(90)
            ->withExpected(
                <<<'DISPLAY'


                    0 mutations were generated:
                           0 mutants were killed by Test Framework

                    Metrics:
                             Mutation Score Indicator (MSI): <high>90%</high>
                             Mutation Code Coverage: <high>90%</high>
                             Covered Code MSI: <high>90%</high>

                    Please note that some mutants will inevitably be harmless (i.e. false positives).

                    DISPLAY,
            )
            ->build();

        yield 'it marks all percentages based on their respective values' => $emptyScenario
            ->withUncovered(true)
            ->withMutationScoreIndicator(40)
            ->withCoverageRate(60)
            ->withCoveredCodeMutationScoreIndicator(95)
            ->withExpected(
                <<<'DISPLAY'


                    0 mutations were generated:
                           0 mutants were killed by Test Framework

                    Metrics:
                             Mutation Score Indicator (MSI): <low>40%</low>
                             Mutation Code Coverage: <medium>60%</medium>
                             Covered Code MSI: <high>95%</high>

                    Please note that some mutants will inevitably be harmless (i.e. false positives).

                    DISPLAY,
            )
            ->build();

        yield 'it rounds all percentages to the next lowest integer' => $emptyScenario
            ->withUncovered(true)
            ->withMutationScoreIndicator(40.55555555)
            ->withCoverageRate(60.55555555)
            ->withCoveredCodeMutationScoreIndicator(95.5555)
            ->withExpected(
                <<<'DISPLAY'


                    0 mutations were generated:
                           0 mutants were killed by Test Framework

                    Metrics:
                             Mutation Score Indicator (MSI): <low>40%</low>
                             Mutation Code Coverage: <medium>60%</medium>
                             Covered Code MSI: <high>95%</high>

                    Please note that some mutants will inevitably be harmless (i.e. false positives).

                    DISPLAY,
            )
            ->build();

        yield 'it pads the counts values' => $emptyScenario
            ->withTotalMutantsCount(10_000)
            ->withKilledByTestsCount(3)
            ->withKilledByIgnoredCount(2000)
            ->withExpected(
                <<<'DISPLAY'


                    10000 mutations were generated:
                           3 mutants were killed by Test Framework
                        2000 mutants were configured to be ignored

                    Metrics:
                             Mutation Code Coverage: <low>0%</low>
                             Covered Code MSI: <low>0%</low>

                    Please note that some mutants will inevitably be harmless (i.e. false positives).

                    DISPLAY,
            )
            ->build();
    }

    public function test_it_calls_the_reporter_when_the_mutation_testing_is_finished(): void
    {
        $showMutationsReporterMock = $this->createMock(Reporter::class);
        $showMutationsReporterMock
            ->expects($this->once())
            ->method('report');

        $reporterMock = $this->createMock(Reporter::class);
        $reporterMock
            ->expects($this->once())
            ->method('report');

        $subscriber = new MutationTestingConsoleLoggerSubscriber(
            new NullOutput(),
            $this->logger,
            $this->metricsCalculator,
            $showMutationsReporterMock,
            $reporterMock,
            withUncovered: true,
        );

        $subscriber->onMutationTestingWasFinished(
            new MutationTestingWasFinished(),
        );
    }

    public function test_it_reacts_on_mutation_testing_finished_and_show_mutations_on(): void
    {
        $this->logger
            ->expects($this->once())
            ->method('finishAnalysis');

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $this->createStub(OutputInterface::class),
            $this->logger,
            $this->metricsCalculator,
            new NullReporter(),
            new NullReporter(),
            withUncovered: true,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());
    }

    private function configureMetricsCalculatorMock(MetricsScenario $scenario): void
    {
        $this->metricsCalculator
            ->method('getKilledByTestsCount')
            ->willReturn($scenario->killedByTestsCount);
        $this->metricsCalculator
            ->method('getKilledByStaticAnalysisCount')
            ->willReturn($scenario->killedByStaticAnalysisCount);
        $this->metricsCalculator
            ->method('getIgnoredCount')
            ->willReturn($scenario->ignoredCount);
        $this->metricsCalculator
            ->method('getNotTestedCount')
            ->willReturn($scenario->notTestedCount);
        $this->metricsCalculator
            ->method('getEscapedCount')
            ->willReturn($scenario->escapedCount);
        $this->metricsCalculator
            ->method('getErrorCount')
            ->willReturn($scenario->errorCount);
        $this->metricsCalculator
            ->method('getSyntaxErrorCount')
            ->willReturn($scenario->syntaxErrorCount);
        $this->metricsCalculator
            ->method('getTimedOutCount')
            ->willReturn($scenario->timedOutCount);
        $this->metricsCalculator
            ->method('getSkippedCount')
            ->willReturn($scenario->skippedCount);
        $this->metricsCalculator
            ->method('getTotalMutantsCount')
            ->willReturn($scenario->totalMutantsCount);
        $this->metricsCalculator
            ->method('getMutationScoreIndicator')
            ->willReturn($scenario->mutationScoreIndicator);
        $this->metricsCalculator
            ->method('getCoverageRate')
            ->willReturn($scenario->coverageRate);
        $this->metricsCalculator
            ->method('getCoveredCodeMutationScoreIndicator')
            ->willReturn($scenario->coveredCodeMutationScoreIndicator);
    }

    private function getDisplay(StreamOutput $output): string
    {
        rewind($output->getStream());

        $display = stream_get_contents($output->getStream());

        return str_replace(PHP_EOL, "\n", $display);
    }
}
