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
use Infection\Logger\MutationAnalysis\MutationAnalysisLogger;
use Infection\Metrics\MetricsCalculator;
use Infection\Mutant\MutantExecutionResult;
use Infection\Process\Runner\ProcessRunner;
use Infection\Reporter\Reporter;
use Infection\Tests\Reporter\NullReporter;
use const PHP_EOL;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function Safe\fopen;
use function Safe\rewind;
use function Safe\stream_get_contents;
use function str_replace;
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

    public function test_it_outputs_metrics_bigger_zero(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        // important metrics, always rendered
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

    public function test_it_shows_mutation_score_indicator_when_flag_is_true(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        // Setup all the necessary mocks similar to existing tests
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
            $this->logger,
            $this->metricsCalculator,
            new NullReporter(),
            new NullReporter(),
            withUncovered: true,
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
            $this->logger,
            $this->metricsCalculator,
            new NullReporter(),
            new NullReporter(),
            withUncovered: false,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $displayOutput = $this->getDisplay($output);

        $this->assertStringNotContainsString('Mutation Score Indicator (MSI):', $displayOutput);
        $this->assertStringContainsString('Mutation Code Coverage: <high>100%</high>', $displayOutput);
        $this->assertStringContainsString('Covered Code MSI: <high>90%</high>', $displayOutput);
    }

    private function getDisplay(StreamOutput $output): string
    {
        rewind($output->getStream());

        $display = stream_get_contents($output->getStream());

        return str_replace(PHP_EOL, "\n", $display);
    }
}
