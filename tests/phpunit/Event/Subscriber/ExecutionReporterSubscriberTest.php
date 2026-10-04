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
use Infection\Event\Events\Application\ApplicationExecutionWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantProcessWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutableFileWasProcessed;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasFinished;
use Infection\Event\Subscriber\ExecutionReporterSubscriber;
use Infection\Report\Execution\ExecutionReportDataProducer;
use Infection\Reporter\Reporter;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExecutionReporterSubscriber::class)]
final class ExecutionReporterSubscriberTest extends TestCase
{
    /**
     * @param non-empty-string $method
     * @param list<mixed> $arguments
     */
    #[DataProvider('eventsProvider')]
    public function test_it_collects_events_and_writes_at_phase_boundaries(object $event, string $method, array $arguments, bool $writes): void
    {
        $producer = $this->createMock(ExecutionReportDataProducer::class);
        $producer->expects($this->once())->method($method)->with(...$arguments);
        $reporter = $this->createMock(Reporter::class);
        $reporter->expects($writes ? $this->once() : $this->never())->method('report');
        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber(new ExecutionReporterSubscriber($producer, $reporter));

        $dispatcher->dispatch($event);
    }

    public static function eventsProvider(): iterable
    {
        yield 'start resets the report' => [new ApplicationExecutionWasStarted(), 'start', [], true];

        yield 'initial command and configuration' => [new InitialTestSuiteWasStarted('command'), 'recordInitialStart', ['command'], true];

        yield 'initial evidence survives a failing test suite' => [new InitialTestSuiteWasFinished('failed'), 'recordInitialFinish', ['failed'], true];

        yield 'coverage does not rewrite the report for each source' => [new MutableFileWasProcessed('/src/Calculator.php', ['hash']), 'recordSource', ['/src/Calculator.php', ['hash']], false];
        $result = MutantExecutionResultBuilder::withMinimalTestData()->build();

        yield 'mutant evidence does not rewrite the report for each mutant' => [new MutantProcessWasFinished($result), 'recordMutant', [$result], false];

        yield 'final evidence is written before cleanup' => [new MutationTestingWasFinished(), 'finish', [], true];
    }
}
