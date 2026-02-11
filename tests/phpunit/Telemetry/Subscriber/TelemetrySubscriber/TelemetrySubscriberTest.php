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

namespace Infection\Tests\Telemetry\Subscriber\TelemetrySubscriber;

use Infection\Event\Events\ArtefactCollection\ArtefactCollectionWasFinished;
use Infection\Event\Events\ArtefactCollection\ArtefactCollectionWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStarted;
use Infection\Event\Events\Ast\AstGenerationWasFinished;
use Infection\Event\Events\Ast\AstGenerationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantProcessWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationHeuristicsWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationHeuristicsWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationForFileWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationForFileWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasStarted;
use Infection\Framework\Iterable\IterableCounter;
use Infection\Logger\MutationAnalysis\TeamCity\NodeIdFactory;
use Infection\Mutation\Mutation;
use Infection\Process\Runner\ProcessRunner;
use Infection\Telemetry\Metric\GarbageCollection\GarbageCollectorInspector;
use Infection\Telemetry\Metric\Memory\MemoryInspector;
use Infection\Telemetry\Metric\ResourceInspector;
use Infection\Telemetry\Metric\Snapshot;
use Infection\Telemetry\Metric\Time\HRTime;
use Infection\Telemetry\Metric\Time\Stopwatch;
use Infection\Telemetry\Subscriber\TelemetrySubscriber;
use Infection\Telemetry\Tracing\RootScope;
use Infection\Telemetry\Tracing\Scope;
use Infection\Telemetry\Tracing\Tracer;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use Infection\Tests\Mutation\MutationBuilder;
use Infection\Tests\Telemetry\Metric\SnapshotBuilder;
use Infection\Tests\TestingUtility\FileSystem\MockSplFileInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

#[CoversClass(TelemetrySubscriber::class)]
final class TelemetrySubscriberTest extends TestCase
{
    private Stopwatch&MockObject $stopwatchMock;

    private MemoryInspector&MockObject $memoryInspectorMock;

    private GarbageCollectorInspector&MockObject $garbageCollectorInspectorMock;

    private Tracer $tracer;

    private TelemetrySubscriber $subscriber;

    protected function setUp(): void
    {
        $this->stopwatchMock = $this->createMock(Stopwatch::class);
        $this->memoryInspectorMock = $this->createMock(MemoryInspector::class);
        $this->garbageCollectorInspectorMock = $this->createMock(GarbageCollectorInspector::class);

        $this->tracer = new Tracer(
            new ResourceInspector(
                $this->stopwatchMock,
                $this->memoryInspectorMock,
                $this->garbageCollectorInspectorMock,
            ),
        );

        $this->subscriber = new TelemetrySubscriber($this->tracer);
    }

    public function test_it_traces_nominal_application_execution(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(1000);
        $this->configureSnapshots(...$snapshots);

        $sourceFile1 = new MockSplFileInfo(realPath: '/path/to/source1.php');
        $sourceFile2 = new MockSplFileInfo(realPath: '/path/to/source2.php');

        $sourceFileId1 = NodeIdFactory::create($sourceFilePath1);
        $sourceFileId2 = NodeIdFactory::create($sourceFilePath2);

        $mutation = $this->createMock(Mutation::class);
        $mutation
            ->method('getHash')
            ->willReturn('mutation-hash-456');
        $mutation
            ->method('getOriginalFilePath')
            ->willReturn($sourceFilePath1);

        $sourceFile = $this->createMock(SplFileInfo::class);
        $sourceFile
            ->method('getRealPath')
            ->willReturn($sourceFilePath1);

        $this->gatherArtefacts();

        $this->runMutationAnalysis(
            $sourceFile1,
            $sourceFile2,
        );

        // Verify the trace structure
        $actualTrace = $this->tracer->getTrace();

        $this->assertCount(2, $actualTrace->spans);

        [$artefactCollectionSpan, $mutationAnalysisSpan] = $actualTrace->spans;

        // Verify artefact collection span
        $this->assertSame(RootScope::ARTEFACT_COLLECTION, $artefactCollectionSpan->scope);
        $this->assertSame($snapshots[0], $artefactCollectionSpan->start);
        $this->assertSame($snapshots[5], $artefactCollectionSpan->end);
        $this->assertCount(2, $artefactCollectionSpan->children);

        [$initialTestSuiteSpan, $initialStaticAnalysisSpan] = $artefactCollectionSpan->children;

        $this->assertSame(Scope::INITIAL_TESTS, $initialTestSuiteSpan->scope);
        $this->assertSame($snapshots[1], $initialTestSuiteSpan->start);
        $this->assertSame($snapshots[2], $initialTestSuiteSpan->end);
        $this->assertEmpty($initialTestSuiteSpan->children);

        $this->assertSame(Scope::INITIAL_STATIC_ANALYSIS, $initialStaticAnalysisSpan->scope);
        $this->assertSame($snapshots[3], $initialStaticAnalysisSpan->start);
        $this->assertSame($snapshots[4], $initialStaticAnalysisSpan->end);
        $this->assertEmpty($initialStaticAnalysisSpan->children);

        // Verify mutation analysis span
        $this->assertSame(RootScope::MUTATION_ANALYSIS, $mutationAnalysisSpan->scope);
        $this->assertSame($snapshots[6], $mutationAnalysisSpan->start);
        $this->assertSame($snapshots[18], $mutationAnalysisSpan->end);
        $this->assertCount(2, $mutationAnalysisSpan->children);

        [$mutationGenerationSpan, $mutationEvaluationSpan] = $mutationAnalysisSpan->children;

        $this->assertSame(Scope::MUTATION_GENERATION, $mutationGenerationSpan->scope);
        $this->assertSame($snapshots[7], $mutationGenerationSpan->start);
        $this->assertSame($snapshots[12], $mutationGenerationSpan->end);
        $this->assertCount(2, $mutationGenerationSpan->children);

        [$astGenerationSpan, $sourceFileMutationGenerationSpan] = $mutationGenerationSpan->children;

        $this->assertSame(Scope::AST_GENERATION, $astGenerationSpan->scope);
        $this->assertSame($snapshots[9], $astGenerationSpan->start);
        $this->assertSame($snapshots[10], $astGenerationSpan->end);
        $this->assertEmpty($astGenerationSpan->children);

        $this->assertSame(Scope::AST_GENERATION, $sourceFileMutationGenerationSpan->scope);
        $this->assertSame($snapshots[11], $sourceFileMutationGenerationSpan->start);
        $this->assertSame($snapshots[12], $sourceFileMutationGenerationSpan->end);
        $this->assertEmpty($sourceFileMutationGenerationSpan->children);

        $this->assertSame(Scope::MUTATION_EVALUATION, $mutationEvaluationSpan->scope);
        $this->assertSame($snapshots[13], $mutationEvaluationSpan->start);
        $this->assertSame($snapshots[17], $mutationEvaluationSpan->end);
        $this->assertEmpty($mutationEvaluationSpan->children);
    }

    private function gatherArtefacts(): void
    {
        $this->subscriber->onArtefactCollectionWasStarted(
            new ArtefactCollectionWasStarted(),
        );

        $this->subscriber->onInitialTestSuiteWasStarted(
            new InitialTestSuiteWasStarted(),
        );
        $this->subscriber->onInitialTestSuiteWasFinished(
            new InitialTestSuiteWasFinished('Test suite output'),
        );

        $this->subscriber->onInitialStaticAnalysisRunWasStarted(
            new InitialStaticAnalysisRunWasStarted(),
        );
        $this->subscriber->onInitialStaticAnalysisRunWasFinished(
            new InitialStaticAnalysisRunWasFinished('Static analysis output'),
        );

        $this->subscriber->onArtefactCollectionWasFinished(
            new ArtefactCollectionWasFinished(),
        );
    }

    private function runMutationAnalysis(
        SplFileInfo $sourceFile1,
        SplFileInfo $sourceFile2,
    ): void
    {
        $trace1 = $this->createMock(Trace::class);
        $trace2 = $this->createMock(Trace::class);

        $mutation1A = MutationBuilder::withMinimalTestData()
            ->withHash('mutation1-A')
            ->build();
        $mutation1B = MutationBuilder::withMinimalTestData()
            ->withHash('mutation1-B')
            ->build();
        $mutation2A = MutationBuilder::withMinimalTestData()
            ->withHash('mutation2-A')
            ->build();

        $this->subscriber->onMutationAnalysisWasStarted(
            new MutationAnalysisWasStarted(),
        );

        $this->subscriber->onMutationGenerationWasStarted(
            new MutationGenerationWasStarted(2),
        );

        $this->subscriber->onAstGenerationWasStarted(
            new AstGenerationWasStarted($sourceFile1->getRealPath()),
        );
        $this->subscriber->onAstGenerationWasFinished(
            new AstGenerationWasFinished($sourceFile2->getRealPath()),
        );

        $this->subscriber->onMutationGenerationForFileWasStarted(
            new MutationGenerationForFileWasStarted(
                $sourceFile1,
                $trace1,
            ),
        );

        $this->subscriber->onMutationTestingWasStarted(
            new MutationTestingWasStarted(
                IterableCounter::UNKNOWN_COUNT,
                $this->createStub(ProcessRunner::class),
            ),
        );

        $this->subscriber->onMutationHeuristicsWasStarted(
            new MutationHeuristicsWasStarted($mutation1A),
        );

        $this->subscriber->onMutationHeuristicsWasFinished(
            new MutationHeuristicsWasFinished($mutation1A, escaped: false),
        );

        $this->subscriber->onMutationHeuristicsWasStarted(
            new MutationHeuristicsWasStarted($mutation1B),
        );

        $this->subscriber->onMutationHeuristicsWasFinished(
            new MutationHeuristicsWasFinished($mutation1B, escaped: true),
        );

        $this->subscriber->onMutantProcessWasFinished(
            new MutantProcessWasFinished(
                MutantExecutionResultBuilder::withMinimalTestData()
                    ->withMutantHash($mutation1B->getHash())
                    ->build(),
            ),
        );

        $this->subscriber->onMutationGenerationForFileWasFinished(
            new MutationGenerationForFileWasFinished(
                $sourceFile1->getRealPath(),
                [
                    $mutation1A->getHash(),
                    $mutation1B->getHash(),
                ],
            ),
        );

        $this->subscriber->onAstGenerationWasStarted(
            new AstGenerationWasStarted($sourceFile2->getRealPath()),
        );
        $this->subscriber->onAstGenerationWasFinished(
            new AstGenerationWasFinished($sourceFile2->getRealPath()),
        );

        $this->subscriber->onMutationGenerationForFileWasStarted(
            new MutationGenerationForFileWasStarted(
                $sourceFile2,
                $trace2,
            ),
        );

        $this->subscriber->onMutationHeuristicsWasStarted(
            new MutationHeuristicsWasStarted($mutation2A),
        );

        $this->subscriber->onMutationHeuristicsWasFinished(
            new MutationHeuristicsWasFinished($mutation2A, escaped: false),
        );

        $this->subscriber->onMutationTestingWasFinished(
            new MutationTestingWasFinished(),
        );

        $this->subscriber->onMutationGenerationForFileWasFinished(
            new MutationGenerationForFileWasFinished(
                $sourceFile2->getRealPath(),
                [$mutation2A->getHash()],
            ),
        );

        $this->subscriber->onMutationGenerationWasFinished(
            new MutationGenerationWasFinished(),
        );

        $this->subscriber->onMutationAnalysisWasFinished(
            new MutationAnalysisWasFinished(),
        );
    }

    private function configureSnapshots(Snapshot ...$snapshots): void
    {
        $times = [];
        $memoryUsages = [];
        $peakMemoryUsages = [];
        $garbageCollectorStatuses = [];

        foreach ($snapshots as $snapshot) {
            $times[] = $snapshot->time;
            $memoryUsages[] = $snapshot->memoryUsage;
            $peakMemoryUsages[] = $snapshot->peakMemoryUsage;
            $garbageCollectorStatuses[] = $snapshot->garbageCollectorStatus;
        }

        $this->stopwatchMock
            ->method('current')
            ->willReturnOnConsecutiveCalls(...$times);

        $this->memoryInspectorMock
            ->method('readMemoryUsage')
            ->willReturnOnConsecutiveCalls(...$memoryUsages);
        $this->memoryInspectorMock
            ->method('readPeakMemoryUsage')
            ->willReturnOnConsecutiveCalls(...$peakMemoryUsages);

        $this->garbageCollectorInspectorMock
            ->method('readStatus')
            ->willReturnOnConsecutiveCalls(...$garbageCollectorStatuses);
    }

    /**
     * @param positive-int $count
     *
     * @return list<Snapshot>
     */
    private static function createSnapshotsWithIncrementalTime(int $count): array
    {
        $previousSnapshot = SnapshotBuilder::withTestData()->build();
        $snapshots = [$previousSnapshot];

        for ($i = 1; $i <= $count; ++$i) {
            $previousSnapshot = SnapshotBuilder::from($previousSnapshot)
                ->withTime(
                    HRTime::fromSecondsAndNanoseconds(
                        $previousSnapshot->time->seconds + 1,
                        $previousSnapshot->time->nanoseconds,
                    ),
                )
                ->build();

            $snapshots[] = $previousSnapshot;
        }

        return $snapshots;
    }
}
