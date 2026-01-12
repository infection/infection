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

use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Differ\DiffColorizer;
use Infection\Event\EventDispatcher\SyncEventDispatcher;
use Infection\Event\MutantProcessWasFinished;
use Infection\Event\MutationTestingWasFinished;
use Infection\Event\MutationTestingWasStarted;
use Infection\Event\Subscriber\MutationTestingConsoleLoggerSubscriber;
use Infection\Logger\FederatedLogger;
use Infection\Logger\FileLogger;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\MutantExecutionResult;
use Infection\Process\Runner\ProcessRunner;
use Infection\Tests\Fixtures\Logger\DummyLineMutationTestingResultsLogger;
use Infection\Tests\Fixtures\Logger\FakeLogger;
use Infection\Tests\Logger\FakeMutationTestingResultsLogger;
use const PHP_EOL;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function Safe\fopen;
use function Safe\rewind;
use function Safe\stream_get_contents;
use function str_replace;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Filesystem\Filesystem;

#[Group('integration')]
#[CoversClass(MutationTestingConsoleLoggerSubscriber::class)]
final class MutationTestingConsoleLoggerSubscriberTest extends TestCase
{
    private MockObject&OutputInterface $output;

    private MockObject&OutputFormatter $outputFormatter;

    private MockObject&MetricsCalculator $metricsCalculator;

    private MockObject&ResultsCollector $resultsCollector;

    private MockObject&DiffColorizer $diffColorizer;

    protected function setUp(): void
    {
        $this->output = $this->createMock(OutputInterface::class);
        $this->outputFormatter = $this->createMock(OutputFormatter::class);
        $this->metricsCalculator = $this->createMock(MetricsCalculator::class);
        $this->resultsCollector = $this->createMock(ResultsCollector::class);
        $this->diffColorizer = $this->createMock(DiffColorizer::class);
    }

    public function test_it_reacts_on_mutation_testing_started(): void
    {
        $this->outputFormatter
            ->expects($this->once())
            ->method('start');

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $this->output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(),
            0,
            withUncovered: true,
            withTimeouts: false,
        ));

        $processRunner = $this->createMock(ProcessRunner::class);

        $dispatcher->dispatch(new MutationTestingWasStarted(1, $processRunner));
    }

    public function test_it_reacts_on_mutation_process_finished(): void
    {
        $this->metricsCalculator
            ->expects($this->never())
            ->method('collect');

        $this->outputFormatter
            ->expects($this->once())
            ->method('advance');

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $this->output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(),
            0,
            withUncovered: true,
            withTimeouts: false,
        ));

        $dispatcher->dispatch(
            new MutantProcessWasFinished(
                $this->createMock(MutantExecutionResult::class),
            ),
        );
    }

    public function test_it_reacts_on_mutation_testing_finished(): void
    {
        $this->outputFormatter
            ->expects($this->once())
            ->method('finish');

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $this->output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(),
            0,
            withUncovered: true,
            withTimeouts: false,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());
    }

    public function test_it_outputs_escaped_and_uncovered_mutants_when_mutation_testing_is_finished(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        $escapedExecutionResult = $this->createMock(MutantExecutionResult::class);
        $escapedExecutionResult->expects($this->once())
            ->method('getOriginalFilePath')
            ->willReturn('/original/filePath');

        $escapedExecutionResult->expects($this->once())
            ->method('getOriginalStartingLine')
            ->willReturn(10);

        $escapedExecutionResult->expects($this->once())
            ->method('getMutatorName')
            ->willReturn('Plus');

        $escapedExecutionResult->expects($this->once())
            ->method('getMutantHash')
            ->willReturn('h4sh');

        $this->resultsCollector->expects($this->once())
            ->method('getEscapedExecutionResults')
            ->willReturn([$escapedExecutionResult]);

        $notCoveredExecutionResult = $this->createMock(MutantExecutionResult::class);

        $this->resultsCollector->expects($this->once())
            ->method('getNotCoveredExecutionResults')
            ->willReturn([$notCoveredExecutionResult]);

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(),
            20,
            withUncovered: true,
            withTimeouts: false,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $resultOutput = $this->getDisplay($output);

        $this->assertStringContainsString(
            "\nEscaped mutants:\n================\n",
            $resultOutput,
        );

        $this->assertStringContainsString(
            "\n\n\n1) /original/filePath:10    [M] Plus [ID] h4sh\n",
            $resultOutput,
        );

        $this->assertStringContainsString(
            "\n\n" . 'Please note that some mutants will inevitably be harmless (i.e. false positives).',
            $resultOutput,
        );

        $this->assertStringContainsString(
            "\nNot covered mutants:\n====================\n",
            $resultOutput,
        );
    }

    public function test_it_does_not_output_escaped_mutants_when_mutation_testing_is_finished_with_no_escaped_mutants(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        // important metrics, always rendered
        $this->resultsCollector->expects($this->once())
            ->method('getEscapedExecutionResults')
            ->willReturn([]);
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
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(),
            20,
            withUncovered: true,
            withTimeouts: false,
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

    public function test_it_outputs_metrics_bigger_zero(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        // important metrics, always rendered
        $this->resultsCollector
            ->expects($this->once())
            ->method('getEscapedExecutionResults')
            ->willReturn([]);
        $this->metricsCalculator
            ->method('getKilledByTestsCount')
            ->willReturn(2);
        // less important metrics, only rendered when > 0
        $this->metricsCalculator
            ->method('getKilledByStaticAnalysisCount')
            ->willReturn(3);
        $this->metricsCalculator
            ->method('getIgnoredCount')
            ->willReturn(1);
        $this->metricsCalculator
            ->method('getNotTestedCount')
            ->willReturn(1);
        $this->metricsCalculator
            ->method('getEscapedCount')
            ->willReturn(1);
        $this->metricsCalculator
            ->method('getErrorCount')
            ->willReturn(1);
        $this->metricsCalculator
            ->method('getSyntaxErrorCount')
            ->willReturn(1);
        $this->metricsCalculator
            ->method('getTimedOutCount')
            ->willReturn(1);
        $this->metricsCalculator
            ->method('getSkippedCount')
            ->willReturn(1);

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(),
            20,
            withUncovered: true,
            withTimeouts: false,
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

        $this->assertStringContainsString(
            '       2 mutants were killed by Test Framework',
            $this->getDisplay($output),
        );
        $this->assertStringContainsString(
            '       3 mutants were caught by Static Analysis',
            $this->getDisplay($output),
        );
        $this->assertStringContainsString(
            '       1 mutants were configured to be ignored',
            $this->getDisplay($output),
        );
        $this->assertStringContainsString(
            '       1 mutants were not covered by tests',
            $this->getDisplay($output),
        );
        $this->assertStringContainsString(
            '       1 covered mutants were not detected',
            $this->getDisplay($output),
        );
        $this->assertStringContainsString(
            '       1 errors were encountered',
            $this->getDisplay($output),
        );
        $this->assertStringContainsString(
            '       1 syntax errors were encountered',
            $this->getDisplay($output),
        );
        $this->assertStringContainsString(
            '       1 time outs were encountered',
            $this->getDisplay($output),
        );
        $this->assertStringContainsString(
            '       1 mutants required more time than configured',
            $this->getDisplay($output),
        );
    }

    public function test_it_outputs_generated_file_log_paths_if_enabled(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(
                new FederatedLogger(
                    new FileLogger(
                        'relative/path.log',
                        new Filesystem(),
                        new DummyLineMutationTestingResultsLogger([]),
                        new FakeLogger(),
                    ),
                    new FileLogger(
                        '/absolute/path.html',
                        new Filesystem(),
                        new DummyLineMutationTestingResultsLogger([]),
                        new FakeLogger(),
                    ),
                    new FakeMutationTestingResultsLogger(),
                ),
                new FakeMutationTestingResultsLogger(),
            ),
            0,
            withUncovered: true,
            withTimeouts: false,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $output = $this->getDisplay($output);
        $this->assertStringContainsString(
            "\n\n" . <<<TEXT
                Generated Reports:
                         - relative/path.log
                         - /absolute/path.html
                TEXT,
            $output,
        );
        $this->assertStringNotContainsString(
            "\n\n" . 'Note: to see escaped mutants run Infection with "--show-mutations=20" or configure file loggers.',
            $output,
        );
    }

    public function test_it_displays_a_tip_to_enable_file_loggers_or_show_mutations_option(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(/* no file loggers */),
            0,
            withUncovered: true,
            withTimeouts: false,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $this->assertStringContainsString(
            "\n\n" . 'Note: to see escaped mutants run Infection with "--show-mutations=20" or configure file loggers.',
            $this->getDisplay($output),
        );
    }

    public function test_tip_is_not_displayed_when_show_mutations_option_is_used(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(/* no file loggers */),
            20,
            withUncovered: true,
            withTimeouts: false,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $this->assertStringNotContainsString(
            "\n\n" . 'Note: to see escaped mutants run Infection with "--show-mutations" or configure file loggers.',
            $this->getDisplay($output),
        );
    }

    public function test_mutations_shortened_renders_count_of_omitted(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        $executionResult = $this->createMock(MutantExecutionResult::class);
        $executionResult->expects($this->once())
            ->method('getOriginalFilePath')
            ->willReturn('/original/filePath');

        $executionResult->expects($this->once())
            ->method('getOriginalStartingLine')
            ->willReturn(10);

        $executionResult->expects($this->once())
            ->method('getMutatorName')
            ->willReturn('Plus');

        $this->resultsCollector->expects($this->once())
            ->method('getEscapedExecutionResults')
            ->willReturn([$executionResult, $executionResult]);

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(),
            1,
            withUncovered: true,
            withTimeouts: false,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $this->assertStringContainsString(
            "\n\n\n" . '... and 1 more mutants were omitted. Use "--show-mutations=max" to see all of them.',
            $this->getDisplay($output),
        );
    }

    public function test_without_mutations_limit(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        $executionResult = $this->createMock(MutantExecutionResult::class);
        $executionResult
            ->expects($this->exactly(3))
            ->method('getOriginalFilePath')
            ->willReturn('/original/filePath');

        $executionResult
            ->expects($this->exactly(3))
            ->method('getOriginalStartingLine')
            ->willReturn(10);

        $executionResult
            ->expects($this->exactly(3))
            ->method('getMutatorName')
            ->willReturn('Plus');

        $this->resultsCollector
            ->expects($this->once())
            ->method('getEscapedExecutionResults')
            ->willReturn([$executionResult, $executionResult, $executionResult]);

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(),
            null,
            withUncovered: true,
            withTimeouts: false,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $this->assertStringNotContainsString(
            'mutants were omitted.',
            $this->getDisplay($output),
        );
    }

    public function test_it_reacts_on_mutation_testing_finished_and_show_mutations_on(): void
    {
        $this->outputFormatter
            ->expects($this->once())
            ->method('finish');

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $this->output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(),
            1,
            withUncovered: true,
            withTimeouts: false,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());
    }

    public function test_it_shows_mutation_score_indicator_when_flag_is_true(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        // Setup all the necessary mocks similar to existing tests
        $this->resultsCollector->method('getEscapedExecutionResults')->willReturn([]);
        $this->metricsCalculator->method('getTotalMutantsCount')->willReturn(10);
        $this->metricsCalculator->method('getKilledByTestsCount')->willReturn(8);
        $this->metricsCalculator->method('getMutationScoreIndicator')->willReturn(80.0);
        $this->metricsCalculator->method('getCoverageRate')->willReturn(100.0);
        $this->metricsCalculator->method('getCoveredCodeMutationScoreIndicator')->willReturn(90.0);
        $this->metricsCalculator->method('getKilledByStaticAnalysisCount')->willReturn(0);
        $this->metricsCalculator->method('getIgnoredCount')->willReturn(0);
        $this->metricsCalculator->method('getNotTestedCount')->willReturn(0);
        $this->metricsCalculator->method('getEscapedCount')->willReturn(0);
        $this->metricsCalculator->method('getErrorCount')->willReturn(0);
        $this->metricsCalculator->method('getSyntaxErrorCount')->willReturn(0);
        $this->metricsCalculator->method('getTimedOutCount')->willReturn(0);
        $this->metricsCalculator->method('getSkippedCount')->willReturn(0);

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(),
            20, // Use 20 like other tests to ensure getEscapedExecutionResults is called
            withUncovered: true,
            withTimeouts: false,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $displayOutput = $this->getDisplay($output);

        $this->assertStringContainsString('Mutation Score Indicator (MSI): <medium>80%</medium>', $displayOutput);
        $this->assertStringContainsString('Mutation Code Coverage: <high>100%</high>', $displayOutput);
        $this->assertStringContainsString('Covered Code MSI: <high>90%</high>', $displayOutput);
    }

    public function test_it_hides_mutation_score_indicator_when_flag_is_false(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        // Setup all the necessary mocks similar to existing tests
        $this->resultsCollector->method('getEscapedExecutionResults')->willReturn([]);
        $this->metricsCalculator->method('getTotalMutantsCount')->willReturn(10);
        $this->metricsCalculator->method('getKilledByTestsCount')->willReturn(8);
        $this->metricsCalculator->method('getMutationScoreIndicator')->willReturn(80.0);
        $this->metricsCalculator->method('getCoverageRate')->willReturn(100.0);
        $this->metricsCalculator->method('getCoveredCodeMutationScoreIndicator')->willReturn(90.0);
        $this->metricsCalculator->method('getKilledByStaticAnalysisCount')->willReturn(0);
        $this->metricsCalculator->method('getIgnoredCount')->willReturn(0);
        $this->metricsCalculator->method('getNotTestedCount')->willReturn(0);
        $this->metricsCalculator->method('getEscapedCount')->willReturn(0);
        $this->metricsCalculator->method('getErrorCount')->willReturn(0);
        $this->metricsCalculator->method('getSyntaxErrorCount')->willReturn(0);
        $this->metricsCalculator->method('getTimedOutCount')->willReturn(0);
        $this->metricsCalculator->method('getSkippedCount')->willReturn(0);

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(),
            20, // Use 20 to ensure getEscapedExecutionResults is called
            withUncovered: false,
            withTimeouts: false,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $displayOutput = $this->getDisplay($output);

        $this->assertStringNotContainsString('Mutation Score Indicator (MSI):', $displayOutput);
        $this->assertStringContainsString('Mutation Code Coverage: <high>100%</high>', $displayOutput);
        $this->assertStringContainsString('Covered Code MSI: <high>90%</high>', $displayOutput);
    }

    public function test_it_does_not_show_timed_out_mutants_by_default(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        $this->resultsCollector
            ->expects($this->once())
            ->method('getEscapedExecutionResults')
            ->willReturn([]);

        // getTimedOutExecutionResults should NOT be called when withTimeouts is false
        $this->resultsCollector
            ->expects($this->never())
            ->method('getTimedOutExecutionResults');

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(),
            20,
            withUncovered: false,
            withTimeouts: false,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $displayOutput = $this->getDisplay($output);

        $this->assertStringNotContainsString('Timed out mutants:', $displayOutput);
    }

    public function test_it_shows_timed_out_mutants_when_with_timeouts_is_true(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        $timedOutExecutionResult = $this->createMock(MutantExecutionResult::class);
        $timedOutExecutionResult
            ->expects($this->once())
            ->method('getOriginalFilePath')
            ->willReturn('/original/timedout/filePath');
        $timedOutExecutionResult
            ->expects($this->once())
            ->method('getOriginalStartingLine')
            ->willReturn(42);
        $timedOutExecutionResult
            ->expects($this->once())
            ->method('getMutatorName')
            ->willReturn('Minus');
        $timedOutExecutionResult
            ->expects($this->once())
            ->method('getMutantHash')
            ->willReturn('t1m30ut');

        $this->resultsCollector
            ->expects($this->once())
            ->method('getEscapedExecutionResults')
            ->willReturn([]);

        $this->resultsCollector
            ->expects($this->once())
            ->method('getTimedOutExecutionResults')
            ->willReturn([$timedOutExecutionResult]);

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(),
            20,
            withUncovered: false,
            withTimeouts: true,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $displayOutput = $this->getDisplay($output);

        $this->assertStringContainsString(
            "\nTimed out mutants:\n==================\n",
            $displayOutput,
        );
        $this->assertStringContainsString(
            '1) /original/timedout/filePath:42    [M] Minus [ID] t1m30ut',
            $displayOutput,
        );
    }

    private function getDisplay(StreamOutput $output): string
    {
        rewind($output->getStream());

        $display = stream_get_contents($output->getStream());

        return str_replace(PHP_EOL, "\n", $display);
    }
}
