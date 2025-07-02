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
use Infection\Tests\WithConsecutive;
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
    /**
     * @var OutputInterface|MockObject
     */
    private $output;

    /**
     * @var OutputFormatter|MockObject
     */
    private $outputFormatter;

    /**
     * @var MetricsCalculator|MockObject
     */
    private $metricsCalculator;

    /**
     * @var ResultsCollector|MockObject
     */
    private $resultsCollector;

    /**
     * @var DiffColorizer|MockObject
     */
    private $diffColorizer;

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
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());
    }

    public function test_it_outputs_escaped_mutants_when_mutation_testing_is_finished(): void
    {
        $this->output
            ->expects($this->atLeastOnce())
            ->method('writeln')
            ->with(...WithConsecutive::create(
                [
                    [
                        '',
                        'Escaped mutants:',
                        '================',
                        '',
                    ],
                ],
                [
                    [
                        '',
                        '1) /original/filePath:10    [M] Plus [ID] h4sh',
                    ],
                ],
            ));

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

        $executionResult->expects($this->once())
            ->method('getMutantHash')
            ->willReturn('h4sh');

        $this->resultsCollector->expects($this->once())
            ->method('getEscapedExecutionResults')
            ->willReturn([$executionResult]);

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $this->output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(),
            20,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());
    }

    public function test_it_does_not_output_escaped_mutants_when_mutation_testing_is_finished_with_no_escaped_mutants(): void
    {
        $this->output
            ->expects($this->atLeastOnce())
            ->method('writeln')
            ->with(...WithConsecutive::create(
                [
                    [
                        '',
                        '',
                    ],
                ],
                [
                    '<options=bold>0</options=bold> mutations were generated:',
                ],
                [
                    '<options=bold>       0</options=bold> mutants were killed by Test Framework',
                ],
                [
                    '<options=bold>       0</options=bold> mutants were caught by Static Analysis',
                ],
            ));

        $this->resultsCollector->expects($this->once())
            ->method('getEscapedExecutionResults')
            ->willReturn([]);

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $this->output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->resultsCollector,
            $this->diffColorizer,
            new FederatedLogger(),
            20,
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());
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
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $output = $this->getDisplay($output);
        $this->assertStringContainsString(
            <<<TEXT
                Generated Reports:
                         - relative/path.log
                         - /absolute/path.html
                TEXT
            ,
            $output,
        );
        $this->assertStringNotContainsString(
            'Note: to see escaped mutants run Infection with "--show-mutations=20" or configure file loggers.',
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
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $this->assertStringContainsString(
            'Note: to see escaped mutants run Infection with "--show-mutations=20" or configure file loggers.',
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
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $this->assertStringNotContainsString(
            'Note: to see escaped mutants run Infection with "--show-mutations" or configure file loggers.',
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
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $this->assertStringContainsString(
            '... and 1 more mutants were omitted. Use "--show-mutations=max" to see all of them.',
            $this->getDisplay($output),
        );
    }

    public function test_without_mutations_limit(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        $executionResult = $this->createMock(MutantExecutionResult::class);
        $executionResult->expects($this->exactly(3))
            ->method('getOriginalFilePath')
            ->willReturn('/original/filePath');

        $executionResult->expects($this->exactly(3))
            ->method('getOriginalStartingLine')
            ->willReturn(10);

        $executionResult->expects($this->exactly(3))
            ->method('getMutatorName')
            ->willReturn('Plus');

        $this->resultsCollector->expects($this->once())
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
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $this->assertStringNotContainsString(
            'mutants were omitted.',
            $this->getDisplay($output),
        );
    }

    public function test_it_reacts_on_mutation_testing_finished_and_show_mutations_on(): void
    {
        $this->output->expects($this->once())
            ->method('getVerbosity');

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
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());
    }

    private function getDisplay(StreamOutput $output): string
    {
        rewind($output->getStream());

        $display = stream_get_contents($output->getStream());

        return str_replace(PHP_EOL, "\n", $display);
    }
}
