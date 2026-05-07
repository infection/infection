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

namespace Infection\Tests\Telemetry\Subscriber;

use function array_map;
use function array_values;
use Infection\Event\Events\Application\ApplicationExecutionWasFinished;
use Infection\Event\Events\Application\ApplicationExecutionWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantProcessWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasStarted;
use Infection\Framework\Iterable\IterableCounter;
use Infection\Mutant\DetectionStatus;
use Infection\Process\Runner\ProcessRunner;
use Infection\Telemetry\OpenTelemetryTracer;
use Infection\Telemetry\Subscriber\OpenTelemetryTracerSubscriber;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use Infection\Tests\Mutation\MutationBuilder;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function sprintf;

#[Group('integration')]
#[CoversClass(OpenTelemetryTracerSubscriber::class)]
final class OpenTelemetryTracerSubscriberTest extends TestCase
{
    // When creating the root span, the parent span is available but has an
    // invalid ID.
    private const string ROOT_SPAN_PARENT_ID = SpanContextValidator::INVALID_SPAN;

    private InMemoryExporter $exporter;

    private TracerProvider $tracerProvider;

    private OpenTelemetryTracerSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->exporter = new InMemoryExporter();
        $this->tracerProvider = new TracerProvider(new SimpleSpanProcessor($this->exporter));

        $this->subscriber = new OpenTelemetryTracerSubscriber(
            new OpenTelemetryTracer(
                $this->tracerProvider->getTracer('infection'),
                $this->tracerProvider,
            ),
        );
    }

    protected function tearDown(): void
    {
        $this->tracerProvider->shutdown();
    }

    public function test_it_exports_the_started_and_finished_spans_with_their_parent_relationships(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-A')
            ->withOriginalFilePath('/path/to/src/Foo.php')
            ->withMutatorName('For_')
            ->build();

        $this->subscriber->onApplicationExecutionWasStarted(new ApplicationExecutionWasStarted());
        $this->subscriber->onInitialTestSuiteWasStarted(new InitialTestSuiteWasStarted());
        $this->subscriber->onInitialTestSuiteWasFinished(new InitialTestSuiteWasFinished('Test suite output'));
        $this->subscriber->onInitialStaticAnalysisRunWasStarted(new InitialStaticAnalysisRunWasStarted());
        $this->subscriber->onInitialStaticAnalysisRunWasFinished(new InitialStaticAnalysisRunWasFinished('Static analysis output'));
        $this->subscriber->onMutationGenerationWasStarted(new MutationGenerationWasStarted(1));
        $this->subscriber->onMutationGenerationWasFinished(new MutationGenerationWasFinished());
        $this->subscriber->onMutationTestingWasStarted(new MutationTestingWasStarted(1, $this->createStub(ProcessRunner::class)));
        $this->subscriber->onMutationEvaluationWasStarted(new MutationEvaluationWasStarted($mutation));
        $this->subscriber->onMutantProcessWasFinished(new MutantProcessWasFinished(
            MutantExecutionResultBuilder::withMinimalTestData()
                ->withMutantHash($mutation->getHash())
                ->withDetectionStatus(DetectionStatus::KILLED_BY_TESTS)
                ->withProcessRuntime(0.123)
                ->build(),
        ));
        $this->subscriber->onMutationTestingWasFinished(new MutationTestingWasFinished());
        $this->subscriber->onApplicationExecutionWasFinished(new ApplicationExecutionWasFinished());

        $this->assertSame(
            [
                'infection.initial_tests',
                'infection.initial_static_analysis',
                'infection.mutation_generation',
                'infection.mutation_evaluation',
                'infection.mutation_testing',
                'infection.run',
            ],
            $this->getExportedSpanNames(),
        );

        $run = $this->getSpanFromExporter('infection.run');
        $initialTests = $this->getSpanFromExporter('infection.initial_tests');
        $initialStaticAnalysis = $this->getSpanFromExporter('infection.initial_static_analysis');
        $mutationGeneration = $this->getSpanFromExporter('infection.mutation_generation');
        $mutationTesting = $this->getSpanFromExporter('infection.mutation_testing');
        $mutationEvaluation = $this->getSpanFromExporter('infection.mutation_evaluation');

        $this->assertSame(self::ROOT_SPAN_PARENT_ID, $run->getParentSpanId());
        $this->assertSame($run->getSpanId(), $initialTests->getParentSpanId());
        $this->assertSame($run->getSpanId(), $initialStaticAnalysis->getParentSpanId());
        $this->assertSame($run->getSpanId(), $mutationGeneration->getParentSpanId());
        $this->assertSame($run->getSpanId(), $mutationTesting->getParentSpanId());
        $this->assertSame($mutationTesting->getSpanId(), $mutationEvaluation->getParentSpanId());

        $this->assertSame(1, $mutationGeneration->getAttributes()->get('infection.source_file.count'));
        $this->assertSame(1, $mutationTesting->getAttributes()->get('infection.mutation.count'));
        $this->assertSame('mutation-A', $mutationEvaluation->getAttributes()->get('infection.mutation.id'));
        $this->assertSame('For_', $mutationEvaluation->getAttributes()->get('infection.mutator.name'));
        $this->assertSame('/path/to/src/Foo.php', $mutationEvaluation->getAttributes()->get('code.file.path'));
        $this->assertSame(10, $mutationEvaluation->getAttributes()->get('code.line.start'));
        $this->assertSame(15, $mutationEvaluation->getAttributes()->get('code.line.end'));
        $this->assertSame(DetectionStatus::KILLED_BY_TESTS->value, $mutationEvaluation->getAttributes()->get('infection.mutation.status'));
        $this->assertSame(0.123, $mutationEvaluation->getAttributes()->get('infection.mutation.runtime'));

        $this->assertAllSpansAreFinished();
        $this->assertTracerProviderWasShutdown();
    }

    public function test_it_ends_open_spans_on_application_finish_even_if_the_finish_events_were_not_emitted(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()->build();

        $this->subscriber->onApplicationExecutionWasStarted(new ApplicationExecutionWasStarted());
        $this->subscriber->onInitialTestSuiteWasStarted(new InitialTestSuiteWasStarted());
        $this->subscriber->onInitialStaticAnalysisRunWasStarted(new InitialStaticAnalysisRunWasStarted());
        $this->subscriber->onMutationGenerationWasStarted(new MutationGenerationWasStarted(1));
        $this->subscriber->onMutationTestingWasStarted(new MutationTestingWasStarted(IterableCounter::UNKNOWN_COUNT, $this->createStub(ProcessRunner::class)));
        $this->subscriber->onMutationEvaluationWasStarted(new MutationEvaluationWasStarted($mutation));
        $this->subscriber->onApplicationExecutionWasFinished(new ApplicationExecutionWasFinished());

        $this->assertSame(
            [
                'infection.initial_tests',
                'infection.initial_static_analysis',
                'infection.mutation_generation',
                'infection.mutation_evaluation',
                'infection.mutation_testing',
                'infection.run',
            ],
            $this->getExportedSpanNames(),
        );

        $this->assertAllSpansAreFinished();
        $this->assertTracerProviderWasShutdown();
    }

    public function test_it_ends_open_mutation_evaluation_spans_on_mutation_testing_finish(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()->build();

        $this->subscriber->onApplicationExecutionWasStarted(new ApplicationExecutionWasStarted());
        $this->subscriber->onMutationTestingWasStarted(new MutationTestingWasStarted(IterableCounter::UNKNOWN_COUNT, $this->createStub(ProcessRunner::class)));
        $this->subscriber->onMutationEvaluationWasStarted(new MutationEvaluationWasStarted($mutation));
        $this->subscriber->onMutationTestingWasFinished(new MutationTestingWasFinished());

        $this->assertSame(
            [
                'infection.mutation_evaluation',
                'infection.mutation_testing',
            ],
            $this->getExportedSpanNames(),
        );

        $this->assertAllSpansAreFinished();
    }

    private function getSpanFromExporter(string $name): SpanDataInterface
    {
        /** @var SpanDataInterface $span */
        foreach ($this->exporter->getSpans() as $span) {
            if ($span->getName() === $name) {
                return $span;
            }
        }

        $this->fail(
            sprintf(
                'Span "%s" was not exported.',
                $name,
            ),
        );
    }

    /**
     * @return list<string>
     */
    private function getExportedSpanNames(): array
    {
        return array_values(
            array_map(
                static fn (SpanDataInterface $span): string => $span->getName(),
                $this->exporter->getSpans(),
            ),
        );
    }

    private function assertAllSpansAreFinished(): void
    {
        /** @var SpanDataInterface $span */
        foreach ($this->exporter->getSpans() as $span) {
            $this->assertTrue(
                $span->hasEnded(),
                sprintf(
                    'Expected the span "%s" to have ended.',
                    $span->getName(),
                ),
            );
        }
    }

    private function assertTracerProviderWasShutdown(): void
    {
        $this->assertFalse($this->tracerProvider->getTracer('infection')->isEnabled());
    }
}
