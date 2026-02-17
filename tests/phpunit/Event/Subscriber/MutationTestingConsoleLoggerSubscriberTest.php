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
use Infection\Mutant\MutantExecutionResult;
use Infection\Process\Runner\ProcessRunner;
use Infection\Reporter\Reporter;
use Infection\Tests\Reporter\NullReporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
#[CoversClass(MutationTestingConsoleLoggerSubscriber::class)]
final class MutationTestingConsoleLoggerSubscriberTest extends TestCase
{
    private MockObject&MutationAnalysisLogger $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(MutationAnalysisLogger::class);
    }

    public function test_it_reacts_on_mutation_testing_started(): void
    {
        $this->logger
            ->expects($this->once())
            ->method('startAnalysis');

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $this->logger,
            new NullReporter(),
            new NullReporter(),
            new NullReporter(),
            new NullReporter(),
        ));

        $processRunner = $this->createStub(ProcessRunner::class);

        $dispatcher->dispatch(new MutationTestingWasStarted(1, $processRunner));
    }

    public function test_it_reacts_on_mutation_process_finished(): void
    {
        $this->logger
            ->expects($this->once())
            ->method('finishEvaluation');

        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $this->logger,
            new NullReporter(),
            new NullReporter(),
            new NullReporter(),
            new NullReporter(),
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
            $this->logger,
            new NullReporter(),
            new NullReporter(),
            new NullReporter(),
            new NullReporter(),
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());
    }

    public function test_it_calls_the_reporter_when_the_mutation_testing_is_finished(): void
    {
        $showMutationsReporterMock = $this->createMock(Reporter::class);
        $showMutationsReporterMock
            ->expects($this->once())
            ->method('report');

        $showMetricsReporterMock = $this->createMock(Reporter::class);
        $showMetricsReporterMock
            ->expects($this->once())
            ->method('report');

        $reporterMock = $this->createMock(Reporter::class);
        $reporterMock
            ->expects($this->once())
            ->method('report');

        $advisoryReporterMock = $this->createMock(Reporter::class);
        $advisoryReporterMock
            ->expects($this->once())
            ->method('report');

        $subscriber = new MutationTestingConsoleLoggerSubscriber(
            $this->logger,
            $showMutationsReporterMock,
            $showMetricsReporterMock,
            $reporterMock,
            $advisoryReporterMock,
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
            $this->logger,
            new NullReporter(),
            new NullReporter(),
            new NullReporter(),
            new NullReporter(),
        ));

        $dispatcher->dispatch(new MutationTestingWasFinished());
    }
}
