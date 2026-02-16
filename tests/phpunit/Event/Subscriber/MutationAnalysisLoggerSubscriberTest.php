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

use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\EventDispatcher\SyncEventDispatcher;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantProcessWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutableFileWasProcessed;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasStarted;
use Infection\Event\Subscriber\MutationAnalysisLoggerSubscriber;
use Infection\Logger\MutationAnalysis\MutationAnalysisLogger;
use Infection\Process\Runner\ProcessRunner;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use Infection\Tests\Mutation\MutationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
#[CoversClass(MutationAnalysisLoggerSubscriber::class)]
final class MutationAnalysisLoggerSubscriberTest extends TestCase
{
    private MockObject&MutationAnalysisLogger $loggerMock;

    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(MutationAnalysisLogger::class);

        $subscriber = new MutationAnalysisLoggerSubscriber(
            $this->loggerMock,
        );

        $this->dispatcher = new SyncEventDispatcher();
        $this->dispatcher->addSubscriber($subscriber);
    }

    public function test_it_reacts_on_mutation_testing_started(): void
    {
        $this->loggerMock
            ->expects($this->once())
            ->method('startAnalysis')
            ->with($this->equalTo(1));

        $this->dispatcher->dispatch(
            new MutationTestingWasStarted(
                1,
                $this->createStub(ProcessRunner::class),
            ),
        );
    }

    public function test_it_reacts_on_mutation_evaluation_was_started(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()->build();

        $this->loggerMock
            ->expects($this->once())
            ->method('startEvaluation')
        ->with($this->identicalTo($mutation));

        $this->dispatcher->dispatch(
            new MutationEvaluationWasStarted($mutation),
        );
    }

    public function test_it_reacts_on_mutable_file_was_processed(): void
    {
        $this->loggerMock
            ->expects($this->once())
            ->method('finishMutationGenerationForFile')
            ->with(
                '/path/to/source.php',
                ['mutationId1', 'mutationId2'],
            );

        $this->dispatcher->dispatch(
            new MutableFileWasProcessed(
                '/path/to/source.php',
                ['mutationId1', 'mutationId2'],
            ),
        );
    }

    public function test_it_does_not_reacts_on_mutable_file_was_processed_if_no_mutation_is_found(): void
    {
        $this->loggerMock
            ->expects($this->never())
            ->method('finishMutationGenerationForFile');

        $this->dispatcher->dispatch(
            new MutableFileWasProcessed(
                sourceFilePath: '/path/to/source.php',
                mutationHashes: [],
            ),
        );
    }

    public function test_it_reacts_on_mutation_process_finished(): void
    {
        $executionResult = MutantExecutionResultBuilder::withMinimalTestData()->build();

        $this->loggerMock
            ->expects($this->once())
            ->method('finishEvaluation')
            ->with($this->identicalTo($executionResult));

        $this->dispatcher->dispatch(
            new MutantProcessWasFinished(
                $executionResult,
            ),
        );
    }

    public function test_it_reacts_on_mutation_testing_finished(): void
    {
        $this->loggerMock
            ->expects($this->once())
            ->method('finishAnalysis');

        $this->dispatcher->dispatch(new MutationTestingWasFinished());
    }
}
