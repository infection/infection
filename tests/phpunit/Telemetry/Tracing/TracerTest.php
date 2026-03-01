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

namespace Infection\Tests\Telemetry\Tracing;

use Infection\Telemetry\Metric\GarbageCollection\GarbageCollectorInspector;
use Infection\Telemetry\Metric\Memory\MemoryInspector;
use Infection\Telemetry\Metric\ResourceInspector;
use Infection\Telemetry\Metric\Snapshot;
use Infection\Telemetry\Metric\Time\HRTime;
use Infection\Telemetry\Metric\Time\Stopwatch;
use Infection\Telemetry\Tracing\RootScope;
use Infection\Telemetry\Tracing\Scope;
use Infection\Telemetry\Tracing\Span;
use Infection\Telemetry\Tracing\SpanBuilder as TelemetrySpanBuilder;
use Infection\Telemetry\Tracing\SpanId;
use Infection\Telemetry\Tracing\Throwable\AlreadyEndedSpan;
use Infection\Telemetry\Tracing\Throwable\AlreadyStartedSpan;
use Infection\Telemetry\Tracing\Throwable\UnendedSpan;
use Infection\Telemetry\Tracing\Tracer;
use Infection\Tests\Telemetry\Metric\SnapshotBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Tracer::class)]
#[CoversClass(TelemetrySpanBuilder::class)]
final class TracerTest extends TestCase
{
    private Stopwatch&MockObject $stopwatchMock;

    private MemoryInspector&MockObject $memoryInspectorMock;

    private GarbageCollectorInspector&MockObject $garbageCollectorInspectorMock;

    private Tracer $tracer;

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
    }

    public function test_it_can_create_a_trace_without_spans(): void
    {
        $this->expectNotToPerformAssertions();

        $this->tracer->getTrace();
    }

    public function test_it_is_not_idempotent(): void
    {
        $trace1 = $this->tracer->getTrace();
        $trace2 = $this->tracer->getTrace();

        $this->assertNotSame($trace1->id, $trace2->id);
    }

    public function test_it_can_create_a_trace_with_one_root_span(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(2);

        $this->configureSnapshots(...$snapshots);

        $span = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'root',
        );
        $this->tracer->endSpan($span);

        $spanBuilt = new Span(
            SpanId::create(
                RootScope::ARTEFACT_COLLECTION,
                'root',
            ),
            'root',
            RootScope::ARTEFACT_COLLECTION,
            $snapshots[0],
            $snapshots[1],
            [],
        );

        $trace = $this->tracer->getTrace();

        $this->assertEquals([$spanBuilt], $trace->spans);
    }

    public function test_it_cannot_create_a_trace_with_the_root_span_not_ended(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(2);

        $this->configureSnapshots(...$snapshots);

        $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'root',
        );

        $this->expectExceptionObject(
            new UnendedSpan(
                'The span "root" for the scope "artefact_collection" was not ended.',
            ),
        );

        $this->tracer->getTrace();
    }

    public function test_it_cannot_end_a_span_more_than_once(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(2);

        $this->configureSnapshots(...$snapshots);

        $span = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'root',
        );
        $this->tracer->endSpan($span);

        $this->expectExceptionObject(
            new AlreadyEndedSpan(
                'The span "root" for the scope "artefact_collection" was already ended.',
            ),
        );

        $this->tracer->endSpan($span);
    }

    public function test_it_can_create_multiple_root_spans(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(3);

        $this->configureSnapshots(...$snapshots);

        $firstSpan = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'first',
        );
        $this->tracer->endSpan($firstSpan);

        $secondSpan = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'second',
        );
        $this->tracer->endSpan($secondSpan);

        $firstRootSpanBuilt = new Span(
            SpanId::create(
                RootScope::ARTEFACT_COLLECTION,
                'first',
            ),
            'first',
            RootScope::ARTEFACT_COLLECTION,
            $snapshots[0],
            $snapshots[1],
            [],
        );
        $secondRootSpanBuilt = new Span(
            SpanId::create(
                RootScope::ARTEFACT_COLLECTION,
                'second',
            ),
            'second',
            RootScope::ARTEFACT_COLLECTION,
            $snapshots[2],
            $snapshots[3],
            [],
        );

        $trace = $this->tracer->getTrace();

        $this->assertEquals([$firstRootSpanBuilt, $secondRootSpanBuilt], $trace->spans);
    }

    public function test_it_can_start_and_end_spans_while_others_are_not_ended_yet(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(3);

        $this->configureSnapshots(...$snapshots);

        $firstSpan = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'first',
        );

        $secondSpan = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'second',
        );

        $this->tracer->endSpan($secondSpan);
        $this->tracer->endSpan($firstSpan);

        $firstRootSpanBuilt = new Span(
            SpanId::create(
                RootScope::ARTEFACT_COLLECTION,
                'first',
            ),
            'first',
            RootScope::ARTEFACT_COLLECTION,
            $snapshots[0],
            $snapshots[3],
            [],
        );
        $secondRootSpanBuilt = new Span(
            SpanId::create(
                RootScope::ARTEFACT_COLLECTION,
                'second',
            ),
            'second',
            RootScope::ARTEFACT_COLLECTION,
            $snapshots[1],
            $snapshots[2],
            [],
        );

        $trace = $this->tracer->getTrace();

        $this->assertEquals([$firstRootSpanBuilt, $secondRootSpanBuilt], $trace->spans);
    }

    public function test_it_can_end_multiple_spans_at_the_same_time(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(3);

        $this->configureSnapshots(...$snapshots);

        $firstSpan = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'first',
        );

        $secondSpan = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'second',
        );

        $this->tracer->endSpan($secondSpan, $firstSpan);

        $firstRootSpanBuilt = new Span(
            SpanId::create(
                RootScope::ARTEFACT_COLLECTION,
                'first',
            ),
            'first',
            RootScope::ARTEFACT_COLLECTION,
            $snapshots[0],
            $snapshots[2],
            [],
        );
        $secondRootSpanBuilt = new Span(
            SpanId::create(
                RootScope::ARTEFACT_COLLECTION,
                'second',
            ),
            'second',
            RootScope::ARTEFACT_COLLECTION,
            $snapshots[1],
            $snapshots[2],
            [],
        );

        $trace = $this->tracer->getTrace();

        $this->assertEquals([$firstRootSpanBuilt, $secondRootSpanBuilt], $trace->spans);
    }

    public function test_it_cannot_start_two_root_spans_with_the_same_id(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(2);

        $this->configureSnapshots(...$snapshots);

        $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'root',
        );
        $this->tracer->startSpan(
            RootScope::SOURCE_FILE,
            'root', // Same id but different scope: this is allowed
        );

        $this->expectExceptionObject(
            new AlreadyStartedSpan(
                'The span "root" for the scope "artefact_collection" was already started.',
            ),
        );

        $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'root',
        );
    }

    public function test_it_can_create_a_child_span(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(4);

        $this->configureSnapshots(...$snapshots);

        $rootSpan = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'root',
        );

        $childSpan = $this->tracer->startChildSpan(
            $rootSpan,
            Scope::INITIAL_TESTS,
            'child',
        );
        $this->tracer->endSpan($childSpan);
        $this->tracer->endSpan($rootSpan);

        $childSpanBuilt = new Span(
            SpanId::create(
                Scope::INITIAL_TESTS,
                'child',
                $rootSpan->id,
            ),
            'child',
            Scope::INITIAL_TESTS,
            $snapshots[1],
            $snapshots[2],
            [],
        );
        $rootSpanBuilt = new Span(
            SpanId::create(
                RootScope::ARTEFACT_COLLECTION,
                'root',
            ),
            'root',
            RootScope::ARTEFACT_COLLECTION,
            $snapshots[0],
            $snapshots[3],
            [$childSpanBuilt],
        );

        $trace = $this->tracer->getTrace();

        $this->assertEquals([$rootSpanBuilt], $trace->spans);
    }

    public function test_it_cannot_end_a_root_span_with_opened_children(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(2);

        $this->configureSnapshots(...$snapshots);

        $rootSpan = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'root',
        );

        $this->tracer->startChildSpan(
            $rootSpan,
            Scope::INITIAL_TESTS,
            'child',
        );

        $this->expectExceptionObject(
            new UnendedSpan(
                'The span "child" for the scope "initial_tests", child of the span "#:artefact_collection:root", was not ended.',
            ),
        );

        $this->tracer->endSpan($rootSpan);
    }

    public function test_it_cannot_end_a_child_span_more_than_once(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(3);

        $this->configureSnapshots(...$snapshots);

        $rootSpan = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'root',
        );

        $childSpan = $this->tracer->startChildSpan(
            $rootSpan,
            Scope::INITIAL_TESTS,
            'child',
        );
        $this->tracer->endSpan($childSpan);

        $this->expectExceptionObject(
            new AlreadyEndedSpan(
                'The span "child" for the scope "initial_tests", child of the span "#:artefact_collection:root", was already ended.',
            ),
        );

        $this->tracer->endSpan($childSpan);
    }

    public function test_it_can_create_multiple_child_spans_on_same_parent(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(6);

        $this->configureSnapshots(...$snapshots);

        $rootSpan = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'root',
        );

        $firstChild = $this->tracer->startChildSpan(
            $rootSpan,
            Scope::INITIAL_TESTS,
            'child1',
        );
        $this->tracer->endSpan($firstChild);

        $secondChild = $this->tracer->startChildSpan(
            $rootSpan,
            Scope::INITIAL_STATIC_ANALYSIS,
            'child2',
        );
        $this->tracer->endSpan($secondChild);

        $this->tracer->endSpan($rootSpan);

        $firstChildSpan = new Span(
            SpanId::create(
                Scope::INITIAL_TESTS,
                'child1',
                $rootSpan->id,
            ),
            'child1',
            Scope::INITIAL_TESTS,
            $snapshots[1],
            $snapshots[2],
            [],
        );
        $secondChildSpan = new Span(
            SpanId::create(
                Scope::INITIAL_STATIC_ANALYSIS,
                'child2',
                $rootSpan->id,
            ),
            'child2',
            Scope::INITIAL_STATIC_ANALYSIS,
            $snapshots[3],
            $snapshots[4],
            [],
        );
        $rootSpanBuilt = new Span(
            SpanId::create(
                RootScope::ARTEFACT_COLLECTION,
                'root',
            ),
            'root',
            RootScope::ARTEFACT_COLLECTION,
            $snapshots[0],
            $snapshots[5],
            [$firstChildSpan, $secondChildSpan],
        );

        $trace = $this->tracer->getTrace();

        $this->assertEquals([$rootSpanBuilt], $trace->spans);
    }

    public function test_it_can_create_nested_child_spans(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(6);

        $this->configureSnapshots(...$snapshots);

        $rootSpan = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'root',
        );

        $childSpan = $this->tracer->startChildSpan(
            $rootSpan,
            Scope::INITIAL_TESTS,
            'child',
        );

        $grandchildSpan = $this->tracer->startChildSpan(
            $childSpan,
            Scope::MUTATION_GENERATION,
            'grandchild',
        );
        $this->tracer->endSpan($grandchildSpan);
        $this->tracer->endSpan($childSpan);
        $this->tracer->endSpan($rootSpan);

        $grandchildSpanBuilt = new Span(
            SpanId::create(
                Scope::MUTATION_GENERATION,
                'grandchild',
                $childSpan->id,
            ),
            'grandchild',
            Scope::MUTATION_GENERATION,
            $snapshots[2],
            $snapshots[3],
            [],
        );
        $childSpanBuilt = new Span(
            SpanId::create(
                Scope::INITIAL_TESTS,
                'child',
                $rootSpan->id,
            ),
            'child',
            Scope::INITIAL_TESTS,
            $snapshots[1],
            $snapshots[4],
            [$grandchildSpanBuilt],
        );
        $rootSpanBuilt = new Span(
            SpanId::create(
                RootScope::ARTEFACT_COLLECTION,
                'root',
            ),
            'root',
            RootScope::ARTEFACT_COLLECTION,
            $snapshots[0],
            $snapshots[5],
            [$childSpanBuilt],
        );

        $trace = $this->tracer->getTrace();

        $this->assertEquals([$rootSpanBuilt], $trace->spans);
    }

    public function test_it_cannot_start_two_child_spans_with_same_id_on_same_parent(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(2);

        $this->configureSnapshots(...$snapshots);

        $rootSpan = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'root',
        );

        $this->tracer->startChildSpan(
            $rootSpan,
            Scope::INITIAL_TESTS,
            'child',
        );

        $this->expectExceptionObject(
            new AlreadyStartedSpan(
                'The span "child" for the scope "initial_tests", child of the span "#:artefact_collection:root", was already started.',
            ),
        );

        $this->tracer->startChildSpan(
            $rootSpan,
            Scope::INITIAL_TESTS,
            'child',
        );
    }

    public function test_it_can_start_child_spans_with_same_id_on_different_parents(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(7);

        $this->configureSnapshots(...$snapshots);

        $firstRoot = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'firstRoot',
        );
        $secondRoot = $this->tracer->startSpan(
            RootScope::MUTATION_ANALYSIS,
            'secondRoot',
        );

        $firstRootChild = $this->tracer->startChildSpan(
            $firstRoot,
            Scope::INITIAL_TESTS,
            'child',
        );
        $this->tracer->endSpan($firstRootChild);

        $secondRootChild = $this->tracer->startChildSpan(
            $secondRoot,
            Scope::INITIAL_TESTS,
            'child',
        );
        $this->tracer->endSpan($secondRootChild);

        $this->tracer->endSpan($firstRoot, $secondRoot);

        $firstRootChildSpan = new Span(
            SpanId::create(
                Scope::INITIAL_TESTS,
                'child',
                $firstRoot->id,
            ),
            'child',
            Scope::INITIAL_TESTS,
            $snapshots[2],
            $snapshots[3],
            [],
        );
        $firstRootSpanBuilt = new Span(
            SpanId::create(
                RootScope::ARTEFACT_COLLECTION,
                'firstRoot',
            ),
            'firstRoot',
            RootScope::ARTEFACT_COLLECTION,
            $snapshots[0],
            $snapshots[6],
            [$firstRootChildSpan],
        );

        $secondRootChildSpan = new Span(
            SpanId::create(
                Scope::INITIAL_TESTS,
                'child',
                $secondRoot->id,
            ),
            'child',
            Scope::INITIAL_TESTS,
            $snapshots[4],
            $snapshots[5],
            [],
        );
        $secondRootSpanBuilt = new Span(
            SpanId::create(
                RootScope::MUTATION_ANALYSIS,
                'secondRoot',
            ),
            'secondRoot',
            RootScope::MUTATION_ANALYSIS,
            $snapshots[1],
            $snapshots[6],
            [$secondRootChildSpan],
        );

        $trace = $this->tracer->getTrace();

        $this->assertEquals([$firstRootSpanBuilt, $secondRootSpanBuilt], $trace->spans);
    }

    public function test_it_can_start_child_spans_with_same_id_but_different_scopes_on_same_parent(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(6);

        $this->configureSnapshots(...$snapshots);

        $rootSpan = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'root',
        );

        $firstChild = $this->tracer->startChildSpan(
            $rootSpan,
            Scope::INITIAL_TESTS,
            'child',
        );
        $this->tracer->endSpan($firstChild);

        $secondChild = $this->tracer->startChildSpan(
            $rootSpan,
            Scope::INITIAL_STATIC_ANALYSIS,
            'child',
        );
        $this->tracer->endSpan($secondChild);

        $this->tracer->endSpan($rootSpan);

        $firstChildSpan = new Span(
            SpanId::create(
                Scope::INITIAL_TESTS,
                'child',
                $rootSpan->id,
            ),
            'child',
            Scope::INITIAL_TESTS,
            $snapshots[1],
            $snapshots[2],
            [],
        );
        $secondChildSpan = new Span(
            SpanId::create(
                Scope::INITIAL_STATIC_ANALYSIS,
                'child',
                $rootSpan->id,
            ),
            'child',
            Scope::INITIAL_STATIC_ANALYSIS,
            $snapshots[3],
            $snapshots[4],
            [],
        );
        $rootSpanBuilt = new Span(
            SpanId::create(
                RootScope::ARTEFACT_COLLECTION,
                'root',
            ),
            'root',
            RootScope::ARTEFACT_COLLECTION,
            $snapshots[0],
            $snapshots[5],
            [$firstChildSpan, $secondChildSpan],
        );

        $trace = $this->tracer->getTrace();

        $this->assertEquals([$rootSpanBuilt], $trace->spans);
    }

    public function test_it_can_end_multiple_child_spans_at_the_same_time(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(5);

        $this->configureSnapshots(...$snapshots);

        $rootSpan = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'root',
        );

        $firstChild = $this->tracer->startChildSpan(
            $rootSpan,
            Scope::INITIAL_TESTS,
            'child1',
        );

        $secondChild = $this->tracer->startChildSpan(
            $rootSpan,
            Scope::INITIAL_STATIC_ANALYSIS,
            'child2',
        );

        $this->tracer->endSpan($firstChild, $secondChild);
        $this->tracer->endSpan($rootSpan);

        $firstChildSpan = new Span(
            SpanId::create(
                Scope::INITIAL_TESTS,
                'child1',
                $rootSpan->id,
            ),
            'child1',
            Scope::INITIAL_TESTS,
            $snapshots[1],
            $snapshots[3],
            [],
        );
        $secondChildSpan = new Span(
            SpanId::create(
                Scope::INITIAL_STATIC_ANALYSIS,
                'child2',
                $rootSpan->id,
            ),
            'child2',
            Scope::INITIAL_STATIC_ANALYSIS,
            $snapshots[2],
            $snapshots[3],
            [],
        );
        $rootSpanBuilt = new Span(
            SpanId::create(
                RootScope::ARTEFACT_COLLECTION,
                'root',
            ),
            'root',
            RootScope::ARTEFACT_COLLECTION,
            $snapshots[0],
            $snapshots[4],
            [$firstChildSpan, $secondChildSpan],
        );

        $trace = $this->tracer->getTrace();

        $this->assertEquals([$rootSpanBuilt], $trace->spans);
    }

    public function test_it_can_generate_a_trace_with_complex_hierarchy(): void
    {
        $snapshots = self::createSnapshotsWithIncrementalTime(14);

        $this->configureSnapshots(...$snapshots);

        $firstRoot = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            'firstRoot',
        );
        $secondRoot = $this->tracer->startSpan(
            RootScope::SOURCE_FILE,
            'secondRoot',
        );

        $firstRootChild1 = $this->tracer->startChildSpan(
            $firstRoot,
            Scope::INITIAL_TESTS,
            'firstRoot-child1',
        );
        $this->tracer->endSpan($firstRootChild1);
        $firstRootChild2 = $this->tracer->startChildSpan(
            $firstRoot,
            Scope::INITIAL_STATIC_ANALYSIS,
            'firstRoot-child2',
        );
        $this->tracer->endSpan($firstRootChild2);

        $secondRootChild1 = $this->tracer->startChildSpan(
            $secondRoot,
            Scope::AST_GENERATION,
            'secondRoot-child1',
        );
        $this->tracer->endSpan($secondRootChild1);
        $secondRootChild2 = $this->tracer->startChildSpan(
            $secondRoot,
            Scope::MUTATION_EVALUATION,
            'secondRoot-child2',
        );
        $secondRootChild2ChildA = $this->tracer->startChildSpan(
            $secondRootChild2,
            Scope::MUTATION_HEURISTICS,
            'secondRoot-child2-childA',
        );
        $this->tracer->endSpan($secondRootChild2ChildA);
        $secondRootChild2ChildB = $this->tracer->startChildSpan(
            $secondRootChild2,
            Scope::MUTANT_EVALUATION,
            'secondRoot-child2-childB',
        );
        $this->tracer->endSpan($secondRootChild2ChildB);
        $this->tracer->endSpan($secondRootChild2);

        $this->tracer->endSpan(
            $firstRoot,
            $secondRoot,
        );

        $firstRootChild1Span = new Span(
            SpanId::create(
                Scope::INITIAL_TESTS,
                'firstRoot-child1',
                $firstRoot->id,
            ),
            'firstRoot-child1',
            Scope::INITIAL_TESTS,
            $snapshots[2],
            $snapshots[3],
            [],
        );
        $firstRootChild2Span = new Span(
            SpanId::create(
                Scope::INITIAL_STATIC_ANALYSIS,
                'firstRoot-child2',
                $firstRoot->id,
            ),
            'firstRoot-child2',
            Scope::INITIAL_STATIC_ANALYSIS,
            $snapshots[4],
            $snapshots[5],
            [],
        );
        $firstRootSpanBuilt = new Span(
            SpanId::create(
                RootScope::ARTEFACT_COLLECTION,
                'firstRoot',
            ),
            'firstRoot',
            RootScope::ARTEFACT_COLLECTION,
            $snapshots[0],
            $snapshots[14],
            [$firstRootChild1Span, $firstRootChild2Span],
        );

        $secondRootChild1Span = new Span(
            SpanId::create(
                Scope::AST_GENERATION,
                'secondRoot-child1',
                $secondRoot->id,
            ),
            'secondRoot-child1',
            Scope::AST_GENERATION,
            $snapshots[6],
            $snapshots[7],
            [],
        );
        $secondRootChild2ChildASpan = new Span(
            SpanId::create(
                Scope::MUTATION_HEURISTICS,
                'secondRoot-child2-childA',
                $secondRootChild2->id,
            ),
            'secondRoot-child2-childA',
            Scope::MUTATION_HEURISTICS,
            $snapshots[9],
            $snapshots[10],
            [],
        );
        $secondRootChild2ChildBSpan = new Span(
            SpanId::create(
                Scope::MUTANT_EVALUATION,
                'secondRoot-child2-childB',
                $secondRootChild2->id,
            ),
            'secondRoot-child2-childB',
            Scope::MUTANT_EVALUATION,
            $snapshots[11],
            $snapshots[12],
            [],
        );
        $secondRootChild2Span = new Span(
            SpanId::create(
                Scope::MUTATION_EVALUATION,
                'secondRoot-child2',
                $secondRoot->id,
            ),
            'secondRoot-child2',
            Scope::MUTATION_EVALUATION,
            $snapshots[8],
            $snapshots[13],
            [$secondRootChild2ChildASpan, $secondRootChild2ChildBSpan],
        );
        $secondRootSpanBuilt = new Span(
            SpanId::create(
                RootScope::SOURCE_FILE,
                'secondRoot',
            ),
            'secondRoot',
            RootScope::SOURCE_FILE,
            $snapshots[1],
            $snapshots[14],
            [$secondRootChild1Span, $secondRootChild2Span],
        );

        $trace = $this->tracer->getTrace();

        $expected = [
            $firstRootSpanBuilt,
            $secondRootSpanBuilt,
        ];

        $this->assertEquals($expected, $trace->spans);
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
