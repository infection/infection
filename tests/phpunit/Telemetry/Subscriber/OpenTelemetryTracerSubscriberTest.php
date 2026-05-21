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
use Infection\AbstractTestFramework\TestFrameworkAdapter;
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
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicSuppressionWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicSuppressionWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantAnalysisWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantAnalysisWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantEvaluation\MutantEvaluationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantEvaluation\MutantEvaluationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantEvaluation\MutantProcessExecutionWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantEvaluation\MutantProcessExecutionWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantMaterialisation\MutantMaterialisationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantMaterialisation\MutantMaterialisationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationForMutationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationForMutationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasStarted;
use Infection\Event\Events\Reporting\ReporterWasFinished;
use Infection\Event\Events\Reporting\ReporterWasStarted;
use Infection\Event\Events\Reporting\ReportingWasFinished;
use Infection\Event\Events\Reporting\ReportingWasStarted;
use Infection\Event\Events\SourceCollection\SourceCollectionWasFinished;
use Infection\Event\Events\SourceCollection\SourceCollectionWasStarted;
use Infection\Framework\InfectionVersion;
use Infection\Framework\Iterable\IterableCounter;
use Infection\Metrics\MetricsCalculator;
use Infection\Mutant\DetectionStatus;
use Infection\Process\MutantProcess;
use Infection\Process\Runner\HeuristicName;
use Infection\Process\Runner\ProcessRunner;
use Infection\Reporter\ReporterName;
use Infection\Telemetry\Attribute\MutationSpanAttributesProvider;
use Infection\Telemetry\Attribute\RunSpanAttributesProvider;
use Infection\Telemetry\OpenTelemetryTracer;
use Infection\Telemetry\ProjectRelativePathResolver;
use Infection\Telemetry\Subscriber\OpenTelemetryTracerSubscriber;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use Infection\Tests\Mutation\MutationBuilder;
use Infection\Tests\Reporter\FakeReporter;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function spl_object_id;
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

    private MetricsCalculator $metricsCalculator;

    protected function setUp(): void
    {
        $this->exporter = new InMemoryExporter();
        $this->tracerProvider = new TracerProvider(new SimpleSpanProcessor($this->exporter));

        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withProjectDirectory('/path/to/project')
            ->build();

        $this->metricsCalculator = new MetricsCalculator(
            $configuration->msiPrecision,
            $configuration->timeoutsAsEscaped,
        );

        $testFrameworkAdapter = $this->createStub(TestFrameworkAdapter::class);
        $testFrameworkAdapter
            ->method('getVersion')
            ->willReturn('12.3.4');
        $projectRelativePathResolver = new ProjectRelativePathResolver($configuration);

        $this->subscriber = new OpenTelemetryTracerSubscriber(
            new OpenTelemetryTracer(
                $this->tracerProvider->getTracer('infection'),
                $this->tracerProvider,
            ),
            new RunSpanAttributesProvider(
                $configuration,
                new InfectionVersion(),
                $testFrameworkAdapter,
                null,
                $this->metricsCalculator,
            ),
            new MutationSpanAttributesProvider($projectRelativePathResolver),
            $projectRelativePathResolver,
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
            ->withOriginalFilePath('/path/to/project/src/Foo.php')
            ->withMutatorName('For_')
            ->build();
        $mutant = MutantBuilder::withMinimalTestData()
            ->withMutation($mutation)
            ->build();

        $process0 = $this->createMock(MutantProcess::class);
        $process0->method('getMutant')->willReturn($mutant);
        $process1 = $this->createMock(MutantProcess::class);
        $process1->method('getMutant')->willReturn($mutant);

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
        $this->subscriber->onAstProcessingWasStarted(new AstProcessingWasStarted('/path/to/project/src/Foo.php'));
        $this->subscriber->onAstParsingWasStarted(new AstParsingWasStarted('/path/to/project/src/Foo.php'));
        $this->subscriber->onAstParsingWasFinished(new AstParsingWasFinished('/path/to/project/src/Foo.php'));
        $this->subscriber->onAstEnrichmentWasStarted(new AstEnrichmentWasStarted('/path/to/project/src/Foo.php'));
        $this->subscriber->onAstEnrichmentWasFinished(new AstEnrichmentWasFinished('/path/to/project/src/Foo.php'));
        $this->subscriber->onAstProcessingWasFinished(new AstProcessingWasFinished('/path/to/project/src/Foo.php'));
        $this->subscriber->onMutationGenerationWasStarted(new MutationGenerationWasStarted(1));
        $this->subscriber->onMutationGenerationWasFinished(new MutationGenerationWasFinished(2, 1));
        $this->subscriber->onMutationEvaluationWasStarted(new MutationEvaluationWasStarted(1, $this->createStub(ProcessRunner::class)));
        $this->subscriber->onMutationEvaluationForMutationWasStarted(new MutationEvaluationForMutationWasStarted($mutation));
        $this->subscriber->onHeuristicSuppressionWasStarted(new HeuristicSuppressionWasStarted($mutation));
        $this->subscriber->onHeuristicWasStarted(new HeuristicWasStarted($mutation, HeuristicName::IGNORED_BY_REGEX));
        $this->subscriber->onHeuristicWasFinished(new HeuristicWasFinished($mutation, HeuristicName::IGNORED_BY_REGEX));
        $this->subscriber->onHeuristicWasStarted(new HeuristicWasStarted($mutation, HeuristicName::UNCOVERED_BY_TESTS));
        $this->subscriber->onHeuristicWasFinished(new HeuristicWasFinished($mutation, HeuristicName::UNCOVERED_BY_TESTS));
        $this->subscriber->onHeuristicSuppressionWasFinished(new HeuristicSuppressionWasFinished($mutation));
        $this->subscriber->onMutantAnalysisWasStarted(new MutantAnalysisWasStarted($mutant));
        $this->subscriber->onMutantMaterialisationWasStarted(new MutantMaterialisationWasStarted($mutant));
        $this->subscriber->onMutantMaterialisationWasFinished(new MutantMaterialisationWasFinished($mutant));
        $this->subscriber->onMutantEvaluationWasStarted(new MutantEvaluationWasStarted($mutant));
        $this->subscriber->onMutantProcessExecutionWasStarted(new MutantProcessExecutionWasStarted($process0));
        $this->subscriber->onMutantProcessExecutionWasFinished(new MutantProcessExecutionWasFinished($process0));
        $this->subscriber->onMutantProcessExecutionWasStarted(new MutantProcessExecutionWasStarted($process1));
        $this->subscriber->onMutantProcessExecutionWasFinished(new MutantProcessExecutionWasFinished($process1));
        $this->subscriber->onMutantEvaluationWasFinished(new MutantEvaluationWasFinished($mutant));
        $this->subscriber->onMutantAnalysisWasFinished(new MutantAnalysisWasFinished($mutant));

        $executionResult = MutantExecutionResultBuilder::withMinimalTestData()
            ->withMutantHash($mutation->getHash())
            ->withDetectionStatus(DetectionStatus::KILLED_BY_TESTS)
            ->withProcessRuntime(0.123)
            ->build();
        $this->metricsCalculator->collect($executionResult);

        $this->subscriber->onMutationEvaluationForMutationWasFinished(new MutationEvaluationForMutationWasFinished($executionResult));
        $fakeReporter = new FakeReporter();
        $reporterId = spl_object_id($fakeReporter);
        $reporterName = ReporterName::FILE_REPORTERS;

        $this->subscriber->onReportingWasStarted(new ReportingWasStarted());
        $this->subscriber->onReporterWasStarted(new ReporterWasStarted($reporterId, $reporterName));
        $this->subscriber->onReporterWasFinished(new ReporterWasFinished($reporterId, $reporterName));
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
                'infection.ast_processing.file.parsing',
                'infection.ast_processing.file.enrichment',
                'infection.ast_processing.file',
                'infection.ast_processing',
                'infection.mutation_generation',
                'infection.mutation_evaluation.mutation.heuristic',
                'infection.mutation_evaluation.mutation.heuristic',
                'infection.mutation_evaluation.mutation.heuristic_suppression',
                'infection.mutation_evaluation.mutant_analysis.materialisation',
                'infection.mutation_evaluation.mutant_analysis.evaluation.process',
                'infection.mutation_evaluation.mutant_analysis.evaluation.process',
                'infection.mutation_evaluation.mutant_analysis.evaluation',
                'infection.mutation_evaluation.mutant_analysis',
                'infection.mutation_evaluation.mutation',
                'infection.reporting.reporter',
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
        $astProcessingFile = $this->getSpanFromExporter('infection.ast_processing.file');
        $astParsing = $this->getSpanFromExporter('infection.ast_processing.file.parsing');
        $astEnrichment = $this->getSpanFromExporter('infection.ast_processing.file.enrichment');
        $mutationGeneration = $this->getSpanFromExporter('infection.mutation_generation');
        $mutationEvaluation = $this->getSpanFromExporter('infection.mutation_evaluation');
        $mutationEvaluationForMutation = $this->getSpanFromExporter('infection.mutation_evaluation.mutation');
        $heuristic = $this->getSpanFromExporter('infection.mutation_evaluation.mutation.heuristic');
        $heuristicSuppression = $this->getSpanFromExporter('infection.mutation_evaluation.mutation.heuristic_suppression');
        $mutantAnalysis = $this->getSpanFromExporter('infection.mutation_evaluation.mutant_analysis');
        $mutantMaterialisation = $this->getSpanFromExporter('infection.mutation_evaluation.mutant_analysis.materialisation');
        $mutantEvaluation = $this->getSpanFromExporter('infection.mutation_evaluation.mutant_analysis.evaluation');
        $process = $this->getSpanFromExporter('infection.mutation_evaluation.mutant_analysis.evaluation.process');
        $reporter = $this->getSpanFromExporter('infection.reporting.reporter');
        $reporting = $this->getSpanFromExporter('infection.reporting');

        $this->assertSame(self::ROOT_SPAN_PARENT_ID, $run->getParentSpanId());
        $this->assertSame($run->getSpanId(), $artefactCollection->getParentSpanId());
        $this->assertSame($artefactCollection->getSpanId(), $initialTests->getParentSpanId());
        $this->assertSame($artefactCollection->getSpanId(), $initialStaticAnalysis->getParentSpanId());
        $this->assertSame($run->getSpanId(), $sourceCollection->getParentSpanId());
        $this->assertSame($run->getSpanId(), $mutationAnalysis->getParentSpanId());
        $this->assertSame($mutationAnalysis->getSpanId(), $astProcessing->getParentSpanId());
        $this->assertSame($astProcessing->getSpanId(), $astProcessingFile->getParentSpanId());
        $this->assertSame($astProcessingFile->getSpanId(), $astParsing->getParentSpanId());
        $this->assertSame($astProcessingFile->getSpanId(), $astEnrichment->getParentSpanId());
        $this->assertSame($mutationAnalysis->getSpanId(), $mutationGeneration->getParentSpanId());
        $this->assertSame($mutationAnalysis->getSpanId(), $mutationEvaluation->getParentSpanId());
        $this->assertSame($mutationEvaluation->getSpanId(), $mutationEvaluationForMutation->getParentSpanId());
        $this->assertSame($mutationEvaluationForMutation->getSpanId(), $heuristicSuppression->getParentSpanId());
        $this->assertSame($heuristicSuppression->getSpanId(), $heuristic->getParentSpanId());
        $this->assertSame($mutationEvaluationForMutation->getSpanId(), $mutantAnalysis->getParentSpanId());
        $this->assertSame($mutantAnalysis->getSpanId(), $mutantMaterialisation->getParentSpanId());
        $this->assertSame($mutantAnalysis->getSpanId(), $mutantEvaluation->getParentSpanId());
        $this->assertSame($mutantEvaluation->getSpanId(), $process->getParentSpanId());
        $this->assertSame($run->getSpanId(), $reporting->getParentSpanId());
        $this->assertSame($reporting->getSpanId(), $reporter->getParentSpanId());

        $this->assertSame(1, $sourceCollection->getAttributes()->get('infection.source_file.count'));
        $this->assertSame($reporterId, $reporter->getAttributes()->get('infection.reporter.id'));
        $this->assertSame('file', $reporter->getAttributes()->get('infection.reporter.name'));
        $this->assertSame(1, $run->getAttributes()->get('infection.source_file.count'));
        $this->assertSame(1, $run->getAttributes()->get('infection.mutated_file.count'));
        $this->assertSame(2, $run->getAttributes()->get('infection.mutation.generated.count'));
        $this->assertSame(1, $run->getAttributes()->get('infection.mutation.evaluated.count'));
        $this->assertSame(1, $run->getAttributes()->get('infection.mutation.suppressed.count'));
        $this->assertSame(1, $run->getAttributes()->get('infection.mutation.eligible.count'));
        $this->assertSame(0, $run->getAttributes()->get('infection.mutation.ineligible.count'));
        $this->assertSame(1, $run->getAttributes()->get('infection.mutation.tested_eligible.count'));
        $this->assertSame(1, $run->getAttributes()->get('infection.mutation.covered.count'));
        $this->assertSame(0, $run->getAttributes()->get('infection.mutation.tested_not_covered.count'));
        $this->assertSame(0, $run->getAttributes()->get('infection.mutation.not_covered.count'));
        $this->assertSame(0, $run->getAttributes()->get('infection.mutation.not_tested.count'));
        $this->assertSame(1, $run->getAttributes()->get('infection.mutation.killed_by_tests.count'));
        $this->assertSame(0, $run->getAttributes()->get('infection.mutation.killed_by_static_analysis.count'));
        $this->assertSame(0, $run->getAttributes()->get('infection.mutation.escaped.count'));
        $this->assertSame(0, $run->getAttributes()->get('infection.mutation.error.count'));
        $this->assertSame(0, $run->getAttributes()->get('infection.mutation.timed_out.count'));
        $this->assertSame(0, $run->getAttributes()->get('infection.mutation.skipped.count'));
        $this->assertSame(0, $run->getAttributes()->get('infection.mutation.syntax_error.count'));
        $this->assertSame(0, $run->getAttributes()->get('infection.mutation.ignored.count'));
        $this->assertSame(100.0, $run->getAttributes()->get('infection.msi.value'));
        $this->assertSame(100.0, $run->getAttributes()->get('infection.mutation.coverage_rate.value'));
        $this->assertSame(100.0, $run->getAttributes()->get('infection.covered_msi.value'));
        $this->assertSame(0.0, $run->getAttributes()->get('infection.msi.threshold'));
        $this->assertSame(0.0, $run->getAttributes()->get('infection.covered_msi.threshold'));
        $this->assertSame('/path/to/project', $run->getAttributes()->get('infection.project.path'));
        $this->assertSame('infection.json5', $run->getAttributes()->get('infection.config.path'));
        $this->assertSame('source', $run->getAttributes()->get('infection.distribution'));
        $this->assertSame(1, $run->getAttributes()->get('infection.thread.count'));
        $this->assertFalse($run->getAttributes()->get('infection.run.source_filtered'));
        $this->assertFalse($run->getAttributes()->get('infection.initial_tests.skipped'));
        $this->assertTrue($run->getAttributes()->get('infection.initial_static_analysis.skipped'));
        $this->assertSame('phpunit', $run->getAttributes()->get('infection.test_framework.name'));
        $this->assertSame('12.3.4', $run->getAttributes()->get('infection.test_framework.version'));
        $this->assertFalse($run->getAttributes()->has('infection.static_analysis_tool.name'));
        $this->assertFalse($run->getAttributes()->has('infection.static_analysis_tool.version'));
        $this->assertIsString($run->getAttributes()->get('infection.version'));
        $this->assertFalse($astProcessing->getAttributes()->has('code.file.path'));
        $this->assertSame('src/Foo.php', $astProcessingFile->getAttributes()->get('code.file.path'));
        $this->assertSame('src/Foo.php', $astParsing->getAttributes()->get('code.file.path'));
        $this->assertSame('src/Foo.php', $astEnrichment->getAttributes()->get('code.file.path'));
        $this->assertSame(1, $mutationGeneration->getAttributes()->get('infection.source_file.count'));
        $this->assertSame(1, $mutationGeneration->getAttributes()->get('infection.mutated_file.count'));
        $this->assertSame(2, $mutationGeneration->getAttributes()->get('infection.mutation.generated.count'));
        $this->assertFalse($mutationEvaluation->getAttributes()->has('infection.mutation.count'));
        $this->assertSame('mutation-A', $mutationEvaluationForMutation->getAttributes()->get('infection.mutation.id'));
        $this->assertSame('For_', $mutationEvaluationForMutation->getAttributes()->get('infection.mutator.name'));
        $this->assertSame('src/Foo.php', $mutationEvaluationForMutation->getAttributes()->get('code.file.path'));
        $this->assertSame(10, $mutationEvaluationForMutation->getAttributes()->get('code.line.start'));
        $this->assertSame(15, $mutationEvaluationForMutation->getAttributes()->get('code.line.end'));

        foreach ([$heuristicSuppression, $heuristic, $mutantAnalysis, $mutantMaterialisation, $mutantEvaluation, $process] as $mutationChildSpan) {
            $this->assertSame('mutation-A', $mutationChildSpan->getAttributes()->get('infection.mutation.id'));
            $this->assertSame('For_', $mutationChildSpan->getAttributes()->get('infection.mutator.name'));
            $this->assertSame('src/Foo.php', $mutationChildSpan->getAttributes()->get('code.file.path'));
            $this->assertSame(10, $mutationChildSpan->getAttributes()->get('code.line.start'));
            $this->assertSame(15, $mutationChildSpan->getAttributes()->get('code.line.end'));
        }

        $this->assertSame(HeuristicName::IGNORED_BY_REGEX->value, $heuristic->getAttributes()->get('infection.mutation_evaluation.heuristic.id'));
        $this->assertSame(DetectionStatus::KILLED_BY_TESTS->value, $mutationEvaluationForMutation->getAttributes()->get('infection.mutation.status'));
        $this->assertSame(0.123, $mutationEvaluationForMutation->getAttributes()->get('infection.mutation.runtime'));

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
        $this->subscriber->onAstProcessingWasStarted(new AstProcessingWasStarted('/path/to/project/src/Foo.php'));
        $this->subscriber->onAstParsingWasStarted(new AstParsingWasStarted('/path/to/project/src/Foo.php'));
        $this->subscriber->onAstEnrichmentWasStarted(new AstEnrichmentWasStarted('/path/to/project/src/Foo.php'));
        $this->subscriber->onMutationGenerationWasStarted(new MutationGenerationWasStarted(1));
        $this->subscriber->onMutationEvaluationWasStarted(new MutationEvaluationWasStarted(IterableCounter::UNKNOWN_COUNT, $this->createStub(ProcessRunner::class)));
        $this->subscriber->onMutationEvaluationForMutationWasStarted(new MutationEvaluationForMutationWasStarted($mutation));
        $this->subscriber->onReportingWasStarted(new ReportingWasStarted());
        $this->subscriber->onReporterWasStarted(new ReporterWasStarted(123, ReporterName::FILE_REPORTERS));
        $this->subscriber->onApplicationExecutionWasFinished(new ApplicationExecutionWasFinished());

        $this->assertSame(
            [
                'infection.ast_processing.file.parsing',
                'infection.ast_processing.file.enrichment',
                'infection.ast_processing.file',
                'infection.ast_processing',
                'infection.initial_tests',
                'infection.initial_static_analysis',
                'infection.artefact_collection',
                'infection.source_collection',
                'infection.mutation_generation',
                'infection.mutation_evaluation.mutation',
                'infection.mutation_evaluation',
                'infection.mutation_analysis',
                'infection.reporting.reporter',
                'infection.reporting',
                'infection.run',
            ],
            $this->getExportedSpanNames(),
        );

        $this->assertAllSpansAreFinished();
        $this->assertTracerProviderWasShutdown();
    }

    public function test_it_ends_open_mutation_evaluation_for_mutation_spans_on_mutation_evaluation_finish(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()->build();

        $this->subscriber->onApplicationExecutionWasStarted(new ApplicationExecutionWasStarted());
        $this->subscriber->onMutationAnalysisWasStarted(new MutationAnalysisWasStarted());
        $this->subscriber->onMutationEvaluationWasStarted(new MutationEvaluationWasStarted(IterableCounter::UNKNOWN_COUNT, $this->createStub(ProcessRunner::class)));
        $this->subscriber->onMutationEvaluationForMutationWasStarted(new MutationEvaluationForMutationWasStarted($mutation));
        $this->subscriber->onMutationEvaluationWasFinished(new MutationEvaluationWasFinished());

        $this->assertSame(
            [
                'infection.mutation_evaluation.mutation',
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
