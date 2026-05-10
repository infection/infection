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
use Infection\Event\Events\ArtefactCollection\ArtefactCollectionWasFinished;
use Infection\Event\Events\ArtefactCollection\ArtefactCollectionWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStarted;
use Infection\Event\Events\Ast\AstEnrichment\AstEnrichmentWasFinished;
use Infection\Event\Events\Ast\AstEnrichment\AstEnrichmentWasStarted;
use Infection\Event\Events\Ast\AstParsing\AstParsingWasFinished;
use Infection\Event\Events\Ast\AstParsing\AstParsingWasStarted;
use Infection\Event\Events\Ast\AstProcessingWasFinished;
use Infection\Event\Events\Ast\AstProcessingWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantEvaluationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantProcessWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasStarted;
use Infection\Event\Events\Reporting\ReportingWasFinished;
use Infection\Event\Events\Reporting\ReportingWasStarted;
use Infection\Event\Events\SourceCollection\SourceCollectionWasFinished;
use Infection\Event\Events\SourceCollection\SourceCollectionWasStarted;
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
        $this->subscriber->onArtefactCollectionWasStarted(new ArtefactCollectionWasStarted());
        $this->subscriber->onInitialTestSuiteWasStarted(new InitialTestSuiteWasStarted());
        $this->subscriber->onInitialTestSuiteWasFinished(new InitialTestSuiteWasFinished('Test suite output'));
        $this->subscriber->onInitialStaticAnalysisRunWasStarted(new InitialStaticAnalysisRunWasStarted());
        $this->subscriber->onInitialStaticAnalysisRunWasFinished(new InitialStaticAnalysisRunWasFinished('Static analysis output'));
        $this->subscriber->onArtefactCollectionWasFinished(new ArtefactCollectionWasFinished());
        $this->subscriber->onSourceCollectionWasStarted(new SourceCollectionWasStarted());
        $this->subscriber->onSourceCollectionWasFinished(new SourceCollectionWasFinished(1));
        $this->subscriber->onMutationAnalysisWasStarted(new MutationAnalysisWasStarted());
        $this->subscriber->onAstProcessingWasStarted(new AstProcessingWasStarted());
        $this->subscriber->onAstParsingWasStarted(new AstParsingWasStarted());
        $this->subscriber->onAstParsingWasFinished(new AstParsingWasFinished());
        $this->subscriber->onAstEnrichmentWasStarted(new AstEnrichmentWasStarted());
        $this->subscriber->onAstEnrichmentWasFinished(new AstEnrichmentWasFinished());
        $this->subscriber->onAstProcessingWasFinished(new AstProcessingWasFinished());
        $this->subscriber->onMutationGenerationWasStarted(new MutationGenerationWasStarted(1));
        $this->subscriber->onMutationGenerationWasFinished(new MutationGenerationWasFinished());
        $this->subscriber->onMutationEvaluationWasStarted(new MutationEvaluationWasStarted(1, $this->createStub(ProcessRunner::class)));
        $this->subscriber->onMutantEvaluationWasStarted(new MutantEvaluationWasStarted($mutation));
        $this->subscriber->onMutantProcessWasFinished(new MutantProcessWasFinished(
            MutantExecutionResultBuilder::withMinimalTestData()
                ->withMutantHash($mutation->getHash())
                ->withDetectionStatus(DetectionStatus::KILLED_BY_TESTS)
                ->withProcessRuntime(0.123)
                ->build(),
        ));
        $this->subscriber->onReportingWasStarted(new ReportingWasStarted());
        $this->subscriber->onReportingWasFinished(new ReportingWasFinished());
        $this->subscriber->onMutationEvaluationWasFinished(new MutationEvaluationWasFinished());
        $this->subscriber->onMutationAnalysisWasFinished(new MutationAnalysisWasFinished());
        $this->subscriber->onApplicationExecutionWasFinished(new ApplicationExecutionWasFinished());

        $this->assertSame(
            [
                'infection.initial_tests',
                'infection.initial_static_analysis',
                'infection.artefact_collection',
                'infection.source_collection',
                'infection.ast_parsing',
                'infection.ast_enrichment',
                'infection.ast_processing',
                'infection.mutation_generation',
                'infection.mutant_evaluation',
                'infection.reporting',
                'infection.mutation_evaluation',
                'infection.mutation_analysis',
                'infection.run',
            ],
            $this->getExportedSpanNames(),
        );

        $run = $this->getSpanFromExporter('infection.run');
        $artefactCollection = $this->getSpanFromExporter('infection.artefact_collection');
        $sourceCollection = $this->getSpanFromExporter('infection.source_collection');
        $initialTests = $this->getSpanFromExporter('infection.initial_tests');
        $initialStaticAnalysis = $this->getSpanFromExporter('infection.initial_static_analysis');
        $mutationAnalysis = $this->getSpanFromExporter('infection.mutation_analysis');
        $astProcessing = $this->getSpanFromExporter('infection.ast_processing');
        $astParsing = $this->getSpanFromExporter('infection.ast_parsing');
        $astEnrichment = $this->getSpanFromExporter('infection.ast_enrichment');
        $mutationGeneration = $this->getSpanFromExporter('infection.mutation_generation');
        $mutationEvaluation = $this->getSpanFromExporter('infection.mutation_evaluation');
        $mutantEvaluation = $this->getSpanFromExporter('infection.mutant_evaluation');
        $reporting = $this->getSpanFromExporter('infection.reporting');

        $this->assertSame(self::ROOT_SPAN_PARENT_ID, $run->getParentSpanId());
        $this->assertSame($run->getSpanId(), $artefactCollection->getParentSpanId());
        $this->assertSame($artefactCollection->getSpanId(), $initialTests->getParentSpanId());
        $this->assertSame($artefactCollection->getSpanId(), $initialStaticAnalysis->getParentSpanId());
        $this->assertSame($run->getSpanId(), $sourceCollection->getParentSpanId());
        $this->assertSame($run->getSpanId(), $mutationAnalysis->getParentSpanId());
        $this->assertSame($mutationAnalysis->getSpanId(), $astProcessing->getParentSpanId());
        $this->assertSame($astProcessing->getSpanId(), $astParsing->getParentSpanId());
        $this->assertSame($astProcessing->getSpanId(), $astEnrichment->getParentSpanId());
        $this->assertSame($mutationAnalysis->getSpanId(), $mutationGeneration->getParentSpanId());
        $this->assertSame($mutationAnalysis->getSpanId(), $mutationEvaluation->getParentSpanId());
        $this->assertSame($mutationEvaluation->getSpanId(), $mutantEvaluation->getParentSpanId());
        $this->assertSame($run->getSpanId(), $reporting->getParentSpanId());

        $this->assertSame(1, $sourceCollection->getAttributes()->get('infection.source_file.count'));
        $this->assertSame(1, $mutationGeneration->getAttributes()->get('infection.source_file.count'));
        $this->assertSame(1, $mutationEvaluation->getAttributes()->get('infection.mutation.count'));
        $this->assertSame('mutation-A', $mutantEvaluation->getAttributes()->get('infection.mutation.id'));
        $this->assertSame('For_', $mutantEvaluation->getAttributes()->get('infection.mutator.name'));
        $this->assertSame('/path/to/src/Foo.php', $mutantEvaluation->getAttributes()->get('code.file.path'));
        $this->assertSame(10, $mutantEvaluation->getAttributes()->get('code.line.start'));
        $this->assertSame(15, $mutantEvaluation->getAttributes()->get('code.line.end'));
        $this->assertSame(DetectionStatus::KILLED_BY_TESTS->value, $mutantEvaluation->getAttributes()->get('infection.mutation.status'));
        $this->assertSame(0.123, $mutantEvaluation->getAttributes()->get('infection.mutation.runtime'));

        $this->assertAllSpansAreFinished();
        $this->assertTracerProviderWasShutdown();
    }

    public function test_it_ends_open_spans_on_application_finish_even_if_the_finish_events_were_not_emitted(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()->build();

        $this->subscriber->onApplicationExecutionWasStarted(new ApplicationExecutionWasStarted());
        $this->subscriber->onArtefactCollectionWasStarted(new ArtefactCollectionWasStarted());
        $this->subscriber->onInitialTestSuiteWasStarted(new InitialTestSuiteWasStarted());
        $this->subscriber->onInitialStaticAnalysisRunWasStarted(new InitialStaticAnalysisRunWasStarted());
        $this->subscriber->onSourceCollectionWasStarted(new SourceCollectionWasStarted());
        $this->subscriber->onMutationAnalysisWasStarted(new MutationAnalysisWasStarted());
        $this->subscriber->onAstProcessingWasStarted(new AstProcessingWasStarted());
        $this->subscriber->onAstParsingWasStarted(new AstParsingWasStarted());
        $this->subscriber->onAstEnrichmentWasStarted(new AstEnrichmentWasStarted());
        $this->subscriber->onMutationGenerationWasStarted(new MutationGenerationWasStarted(1));
        $this->subscriber->onMutationEvaluationWasStarted(new MutationEvaluationWasStarted(IterableCounter::UNKNOWN_COUNT, $this->createStub(ProcessRunner::class)));
        $this->subscriber->onMutantEvaluationWasStarted(new MutantEvaluationWasStarted($mutation));
        $this->subscriber->onReportingWasStarted(new ReportingWasStarted());
        $this->subscriber->onApplicationExecutionWasFinished(new ApplicationExecutionWasFinished());

        $this->assertSame(
            [
                'infection.initial_tests',
                'infection.initial_static_analysis',
                'infection.artefact_collection',
                'infection.source_collection',
                'infection.ast_parsing',
                'infection.ast_enrichment',
                'infection.ast_processing',
                'infection.mutation_generation',
                'infection.mutant_evaluation',
                'infection.mutation_evaluation',
                'infection.mutation_analysis',
                'infection.reporting',
                'infection.run',
            ],
            $this->getExportedSpanNames(),
        );

        $this->assertAllSpansAreFinished();
        $this->assertTracerProviderWasShutdown();
    }

    public function test_it_ends_open_mutant_evaluation_spans_on_mutation_evaluation_finish(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()->build();

        $this->subscriber->onApplicationExecutionWasStarted(new ApplicationExecutionWasStarted());
        $this->subscriber->onMutationAnalysisWasStarted(new MutationAnalysisWasStarted());
        $this->subscriber->onMutationEvaluationWasStarted(new MutationEvaluationWasStarted(IterableCounter::UNKNOWN_COUNT, $this->createStub(ProcessRunner::class)));
        $this->subscriber->onMutantEvaluationWasStarted(new MutantEvaluationWasStarted($mutation));
        $this->subscriber->onMutationEvaluationWasFinished(new MutationEvaluationWasFinished());

        $this->assertSame(
            [
                'infection.mutant_evaluation',
                'infection.mutation_evaluation',
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
