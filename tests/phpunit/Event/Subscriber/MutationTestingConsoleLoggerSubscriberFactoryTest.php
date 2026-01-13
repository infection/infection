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
use Infection\Event\MutationTestingWasFinished;
use Infection\Event\Subscriber\MutationTestingConsoleLoggerSubscriber;
use Infection\Event\Subscriber\MutationTestingConsoleLoggerSubscriberFactory;
use Infection\Framework\Str;
use Infection\Logger\FederatedLogger;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\MutantExecutionResult;
use Infection\Tests\Fixtures\Console\FakeOutputFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function Safe\fopen;
use function Safe\rewind;
use function Safe\stream_get_contents;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

#[CoversClass(MutationTestingConsoleLoggerSubscriberFactory::class)]
#[Group('integration')]
final class MutationTestingConsoleLoggerSubscriberFactoryTest extends TestCase
{
    private MockObject&MetricsCalculator $metricsCalculatorMock;

    private MockObject&ResultsCollector $resultsCollectorMock;

    private MockObject&DiffColorizer $diffColorizerMock;

    protected function setUp(): void
    {
        $this->metricsCalculatorMock = $this->createMock(MetricsCalculator::class);
        $this->metricsCalculatorMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $this->resultsCollectorMock = $this->createMock(ResultsCollector::class);
        $this->resultsCollectorMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $this->diffColorizerMock = $this->createMock(DiffColorizer::class);
        $this->diffColorizerMock
            ->expects($this->never())
            ->method($this->anything())
        ;
    }

    #[DataProvider('showMutationsProvider')]
    public function test_it_creates_a_subscriber(?int $numberOfShownMutations): void
    {
        $factory = new MutationTestingConsoleLoggerSubscriberFactory(
            $this->metricsCalculatorMock,
            $this->resultsCollectorMock,
            $this->diffColorizerMock,
            new FederatedLogger(),
            $numberOfShownMutations,
            new FakeOutputFormatter(),
            withUncovered: true,
            withTimeouts: false,
        );

        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock
            ->method('isDecorated')
            ->willReturn(false)
        ;

        $subscriber = $factory->create($outputMock);

        $this->assertInstanceOf(MutationTestingConsoleLoggerSubscriber::class, $subscriber);
    }

    public static function showMutationsProvider(): iterable
    {
        foreach ([20, 0, null] as $showMutations) {
            yield [$showMutations];
        }
    }

    public function test_it_creates_a_subscriber_without_timeouts_by_default(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        $metricsCalculator = $this->createMock(MetricsCalculator::class);
        $resultsCollector = $this->createMock(ResultsCollector::class);
        $diffColorizer = $this->createMock(DiffColorizer::class);
        $outputFormatter = $this->createMock(OutputFormatter::class);

        $resultsCollector->expects($this->once())
            ->method('getEscapedExecutionResults')
            ->willReturn([]);

        // getTimedOutExecutionResults should NOT be called when factory uses default withTimeouts=false
        $resultsCollector->expects($this->never())
            ->method('getTimedOutExecutionResults');

        $factory = new MutationTestingConsoleLoggerSubscriberFactory(
            $metricsCalculator,
            $resultsCollector,
            $diffColorizer,
            new FederatedLogger(),
            20,
            $outputFormatter,
            withUncovered: false,
            withTimeouts: false,
        );

        $subscriber = $factory->create($output);

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber($subscriber);

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $displayOutput = $this->getDisplay($output);

        $this->assertStringNotContainsString('Timed out mutants:', $displayOutput);
    }

    public function test_it_creates_a_subscriber_with_timeouts_when_explicitly_enabled(): void
    {
        $output = new StreamOutput(fopen('php://memory', 'w'));

        $metricsCalculator = $this->createMock(MetricsCalculator::class);
        $resultsCollector = $this->createMock(ResultsCollector::class);
        $diffColorizer = $this->createMock(DiffColorizer::class);
        $outputFormatter = $this->createMock(OutputFormatter::class);

        $timedOutExecutionResult = $this->createMock(MutantExecutionResult::class);
        $timedOutExecutionResult->expects($this->once())
            ->method('getOriginalFilePath')
            ->willReturn('/original/timedout/filePath');
        $timedOutExecutionResult->expects($this->once())
            ->method('getOriginalStartingLine')
            ->willReturn(42);
        $timedOutExecutionResult->expects($this->once())
            ->method('getMutatorName')
            ->willReturn('Minus');
        $timedOutExecutionResult->expects($this->once())
            ->method('getMutantHash')
            ->willReturn('t1m30ut');

        $resultsCollector->expects($this->once())
            ->method('getEscapedExecutionResults')
            ->willReturn([]);

        $resultsCollector->expects($this->once())
            ->method('getTimedOutExecutionResults')
            ->willReturn([$timedOutExecutionResult]);

        $factory = new MutationTestingConsoleLoggerSubscriberFactory(
            $metricsCalculator,
            $resultsCollector,
            $diffColorizer,
            new FederatedLogger(),
            20,
            $outputFormatter,
            withUncovered: false,
            withTimeouts: true,
        );

        $subscriber = $factory->create($output);

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber($subscriber);

        $dispatcher->dispatch(new MutationTestingWasFinished());

        $displayOutput = $this->getDisplay($output);

        $this->assertStringContainsString(
            'Timed out mutants:',
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

        return Str::toUnixLineEndings(stream_get_contents($output->getStream()));
    }
}
