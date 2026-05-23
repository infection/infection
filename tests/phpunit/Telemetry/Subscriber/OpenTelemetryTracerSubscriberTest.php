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
use Infection\Event\EventDispatcher\SyncEventDispatcher;
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
use Infection\Mutant\Mutant;
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
use Infection\Tests\Mutant\DummyMutantExecutionResultFactory;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use Infection\Tests\Mutation\MutationBuilder;
use Infection\Tests\Process\Runner\NullProcessRunner;
use Infection\Tests\Reporter\FakeReporter;
use Infection\Tests\Telemetry\SDK\Clock\FakeClock;
use Infection\Tests\Telemetry\SDK\Clock\IncrementalClock;
use Infection\Tests\Telemetry\SDK\Trace\SpanExporter\TestExporter;
use Infection\Tests\Telemetry\SDK\Trace\Tracer\GuardedTracer;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Common\Time\TestClock;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function spl_object_id;
use function sprintf;
use Symfony\Component\Process\Process;

#[Group('integration')]
#[CoversClass(OpenTelemetryTracerSubscriber::class)]
final class OpenTelemetryTracerSubscriberTest extends TestCase
{
    // When creating the root span, the parent span is available but has an
    // invalid ID.
    private const string ROOT_SPAN_PARENT_ID = SpanContextValidator::INVALID_SPAN;

    private TestExporter $exporter;

    private TracerProvider $tracerProvider;

    private OpenTelemetryTracerSubscriber $subscriber;

    private GuardedTracer $guardedTracer;

    private MetricsCalculator $metricsCalculator;

    private TestClock $clock;

    protected function setUp(): void
    {
        $this->exporter = new TestExporter();
        $this->tracerProvider = new TracerProvider(
            new SimpleSpanProcessor($this->exporter),
        );
        $this->clock = new TestClock(1_000_000_000);
        Clock::setDefault(new FakeClock());

        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withProjectDirectory('/path/to/project')
            ->build();

        $this->metricsCalculator = new MetricsCalculator(
            $configuration->msiPrecision,
            $configuration->timeoutsAsEscaped,
        );

        $this->subscriber = $this->createSubscriber($this->clock);
    }

    protected function tearDown(): void
    {
        $this->tracerProvider->shutdown();
        Clock::reset();
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

        $process0 = $this->createMutantProcess($mutant, 0);
        $process1 = $this->createMutantProcess($mutant, 1, true);

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
        $this->clock->setTime(2_000_000_000);
        $this->subscriber->onMutantEvaluationWasStarted(new MutantEvaluationWasStarted($mutant));
        $this->clock->setTime(2_000_000_010);
        $this->subscriber->onMutantProcessExecutionWasStarted(new MutantProcessExecutionWasStarted($process0, 1));
        $this->subscriber->onMutantProcessExecutionWasFinished(new MutantProcessExecutionWasFinished($process0));
        $this->subscriber->onMutantProcessExecutionWasStarted(new MutantProcessExecutionWasStarted($process1, 2));
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
        $secondProcess = $this->getMutantProcessExecutionSpanForThread(2);
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

        $this->assertSpanEventClasses($run, ApplicationExecutionWasStarted::class, ApplicationExecutionWasFinished::class);
        $this->assertSpanEventClasses($artefactCollection, ArtefactCollectionWasStarted::class, ArtefactCollectionWasFinished::class);
        $this->assertSpanEventClasses($sourceCollection, SourceCollectionWasStarted::class, SourceCollectionWasFinished::class);
        $this->assertSpanEventClasses($initialTests, InitialTestSuiteWasStarted::class, InitialTestSuiteWasFinished::class);
        $this->assertSpanEventClasses($initialStaticAnalysis, InitialStaticAnalysisRunWasStarted::class, InitialStaticAnalysisRunWasFinished::class);
        $this->assertSpanEventClasses($mutationAnalysis, MutationAnalysisWasStarted::class, MutationAnalysisWasFinished::class);
        $this->assertSpanEventClasses($astProcessing, AstProcessingWasStarted::class, MutationGenerationWasStarted::class);
        $this->assertSpanEventClasses($astProcessingFile, AstProcessingWasStarted::class, AstProcessingWasFinished::class);
        $this->assertSpanEventClasses($astParsing, AstParsingWasStarted::class, AstParsingWasFinished::class);
        $this->assertSpanEventClasses($astEnrichment, AstEnrichmentWasStarted::class, AstEnrichmentWasFinished::class);
        $this->assertSpanEventClasses($mutationGeneration, MutationGenerationWasStarted::class, MutationGenerationWasFinished::class);
        $this->assertSpanEventClasses($mutationEvaluation, MutationEvaluationWasStarted::class, MutationEvaluationWasFinished::class);
        $this->assertSpanEventClasses($mutationEvaluationForMutation, MutationEvaluationForMutationWasStarted::class, MutationEvaluationForMutationWasFinished::class);
        $this->assertSpanEventClasses($heuristic, HeuristicWasStarted::class, HeuristicWasFinished::class);
        $this->assertSpanEventClasses($heuristicSuppression, HeuristicSuppressionWasStarted::class, HeuristicSuppressionWasFinished::class);
        $this->assertSpanEventClasses($mutantAnalysis, MutantAnalysisWasStarted::class, MutantAnalysisWasFinished::class);
        $this->assertSpanEventClasses($mutantMaterialisation, MutantMaterialisationWasStarted::class, MutantMaterialisationWasFinished::class);
        $this->assertSpanEventClasses($mutantEvaluation, MutantEvaluationWasStarted::class, MutantEvaluationWasFinished::class);
        $this->assertSpanEventClasses($process, MutantProcessExecutionWasStarted::class, MutantProcessExecutionWasFinished::class);
        $this->assertSpanEventClasses($secondProcess, MutantProcessExecutionWasStarted::class, MutantProcessExecutionWasFinished::class);
        $this->assertSpanEventClasses($reporting, ReportingWasStarted::class, ReportingWasFinished::class);
        $this->assertSpanEventClasses($reporter, ReporterWasStarted::class, ReporterWasFinished::class);

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
        $this->assertSame(0, $process->getAttributes()->get('process.exit.code'));
        $this->assertFalse($process->getAttributes()->get('infection.mutation.process.timed_out'));
        $this->assertSame('phpunit', $process->getAttributes()->get('infection.mutation.process.test_framework'));
        $this->assertSame(1, $process->getAttributes()->get('infection.mutation.process.thread'));
        $this->assertSame(1, $secondProcess->getAttributes()->get('process.exit.code'));
        $this->assertTrue($secondProcess->getAttributes()->get('infection.mutation.process.timed_out'));
        $this->assertSame('phpunit', $secondProcess->getAttributes()->get('infection.mutation.process.test_framework'));
        $this->assertSame(2, $secondProcess->getAttributes()->get('infection.mutation.process.thread'));
        $this->assertIsFloat($mutantEvaluation->getAttributes()->get('infection.mutation.queue_wait.duration'));
        $this->assertGreaterThanOrEqual(0.0, $mutantEvaluation->getAttributes()->get('infection.mutation.queue_wait.duration'));
        $this->assertSame(DetectionStatus::KILLED_BY_TESTS->value, $mutationEvaluationForMutation->getAttributes()->get('infection.mutation.status'));
        $this->assertSame('covered', $mutationEvaluationForMutation->getAttributes()->get('infection.mutation.msi.category'));
        $this->assertSame(0.123, $mutationEvaluationForMutation->getAttributes()->get('infection.mutation.runtime'));

        $this->exporter->assertAllSpansAreFinished();
        $this->guardedTracer->assertHasNoOpenSpans();
        $this->assertTracerProviderWasShutdown();
    }

    /**
     * @param list<object> $events
     */
    #[DataProvider('spanTreeScenarioProvider')]
    public function test_it_can_describe_the_exported_span_tree_with_timings(
        array $events,
        string $expected,
    ): void {
        $this->recordEvents(
            $this->createSubscriber(
                new IncrementalClock(10, 10),
            ),
            $events,
        );

        $actual = SpanTreeRenderer::render($this->exporter->getSpans());

        $this->assertSame($expected, $actual);
        $this->guardedTracer->assertHasNoOpenSpans();
    }

    public static function spanTreeScenarioProvider(): iterable
    {
        $mutationA = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-A')
            ->build();
        $mutationB = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-B')
            ->build();
        $mutantA = MutantBuilder::withMinimalTestData()
            ->withMutation($mutationA)
            ->build();
        $mutantB = MutantBuilder::withMinimalTestData()
            ->withMutation($mutationB)
            ->build();

        $mutationAProcess1 = self::createFinishedMutantProcess($mutantA);
        $mutationAProcess2 = self::createFinishedMutantProcess($mutantA);
        $mutationBProcess1 = self::createFinishedMutantProcess($mutantB);
        $mutationBProcess2 = self::createFinishedMutantProcess($mutantB);

        $noProgressMutationAProcess1 = self::createFinishedMutantProcess($mutantA);
        $noProgressMutationAProcess2 = self::createFinishedMutantProcess($mutantA);
        $noProgressMutationBProcess1 = self::createFinishedMutantProcess($mutantB);
        $noProgressMutationBProcess2 = self::createFinishedMutantProcess($mutantB);

        yield 'run with artefact collection' => [
            [
                new ApplicationExecutionWasStarted(),
                new ArtefactCollectionWasStarted(),
                new ArtefactCollectionWasFinished(),
                new ApplicationExecutionWasFinished(),
            ],
            <<<'TXT'
                infection.run [10, 40]
                  infection.artefact_collection [20, 30]
                TXT,
        ];

        yield 'complete cycle with two basic mutations' => [
            [
                new ApplicationExecutionWasStarted(),
                new ArtefactCollectionWasStarted(),
                new InitialTestSuiteWasStarted(),
                new InitialTestSuiteWasFinished('Test suite output'),
                new InitialStaticAnalysisRunWasStarted(),
                new InitialStaticAnalysisRunWasFinished('Static analysis output'),
                new ArtefactCollectionWasFinished(),
                new SourceCollectionWasStarted(),
                new SourceCollectionWasFinished(1),
                new MutationAnalysisWasStarted(),
                new MutationGenerationWasStarted(1),
                new AstProcessingWasStarted('/path/to/project/src/Foo.php'),
                new AstParsingWasStarted('/path/to/project/src/Foo.php'),
                new AstParsingWasFinished('/path/to/project/src/Foo.php'),
                new AstEnrichmentWasStarted('/path/to/project/src/Foo.php'),
                new AstEnrichmentWasFinished('/path/to/project/src/Foo.php'),
                new AstProcessingWasFinished('/path/to/project/src/Foo.php'),
                new MutationGenerationWasFinished(2, 1),
                new MutationEvaluationWasStarted(2, new NullProcessRunner()),
                new MutationEvaluationForMutationWasStarted($mutationA),
                new HeuristicSuppressionWasStarted($mutationA),
                new HeuristicWasStarted($mutationA, HeuristicName::IGNORED_BY_REGEX),
                new HeuristicWasFinished($mutationA, HeuristicName::IGNORED_BY_REGEX),
                new HeuristicWasStarted($mutationA, HeuristicName::UNCOVERED_BY_TESTS),
                new HeuristicWasFinished($mutationA, HeuristicName::UNCOVERED_BY_TESTS),
                new HeuristicSuppressionWasFinished($mutationA),
                new MutantAnalysisWasStarted($mutantA),
                new MutantMaterialisationWasStarted($mutantA),
                new MutantMaterialisationWasFinished($mutantA),
                new MutationEvaluationForMutationWasStarted($mutationB),
                new HeuristicSuppressionWasStarted($mutationB),
                new HeuristicWasStarted($mutationB, HeuristicName::IGNORED_BY_REGEX),
                new HeuristicWasFinished($mutationB, HeuristicName::IGNORED_BY_REGEX),
                new HeuristicWasStarted($mutationB, HeuristicName::UNCOVERED_BY_TESTS),
                new HeuristicWasFinished($mutationB, HeuristicName::UNCOVERED_BY_TESTS),
                new HeuristicSuppressionWasFinished($mutationB),
                new MutantAnalysisWasStarted($mutantB),
                new MutantMaterialisationWasStarted($mutantB),
                new MutantMaterialisationWasFinished($mutantB),
                new MutantEvaluationWasStarted($mutantA),
                new MutantProcessExecutionWasStarted($mutationAProcess1, 1),
                new MutantProcessExecutionWasFinished($mutationAProcess1),
                new MutantProcessExecutionWasStarted($mutationAProcess2, 2),
                new MutantProcessExecutionWasFinished($mutationAProcess2),
                new MutantEvaluationWasFinished($mutantA),
                new MutantAnalysisWasFinished($mutantA),
                new MutationEvaluationForMutationWasFinished(
                    MutantExecutionResultBuilder::withMinimalTestData()
                        ->withMutantHash('mutation-A')
                        ->build(),
                ),
                new MutantEvaluationWasStarted($mutantB),
                new MutantProcessExecutionWasStarted($mutationBProcess1, 1),
                new MutantProcessExecutionWasFinished($mutationBProcess1),
                new MutantProcessExecutionWasStarted($mutationBProcess2, 2),
                new MutantProcessExecutionWasFinished($mutationBProcess2),
                new MutantEvaluationWasFinished($mutantB),
                new MutantAnalysisWasFinished($mutantB),
                new MutationEvaluationForMutationWasFinished(
                    MutantExecutionResultBuilder::withMinimalTestData()
                        ->withMutantHash('mutation-B')
                        ->build(),
                ),
                new ReportingWasStarted(),
                new ReporterWasStarted(123, ReporterName::FILE_REPORTERS),
                new ReporterWasFinished(123, ReporterName::FILE_REPORTERS),
                new ReporterWasStarted(456, ReporterName::SHOW_METRICS),
                new ReporterWasFinished(456, ReporterName::SHOW_METRICS),
                new ReportingWasFinished(),
                new MutationEvaluationWasFinished(),
                new MutationAnalysisWasFinished(),
                new ApplicationExecutionWasFinished(),
            ],
            <<<'TXT'
                infection.run [10, 660]
                  infection.artefact_collection [20, 70]
                    infection.initial_tests [30, 40]
                    infection.initial_static_analysis [50, 60]
                  infection.source_collection [80, 90]
                  infection.mutation_analysis [100, 650]
                    infection.mutation_generation [110, 190]
                    infection.ast_processing [120, 640]
                      infection.ast_processing.file [130, 180]
                        infection.ast_processing.file.parsing [140, 150]
                        infection.ast_processing.file.enrichment [160, 170]
                    infection.mutation_evaluation [200, 630]
                      infection.mutation_evaluation.mutation [210, 480]
                        infection.mutation_evaluation.mutation.heuristic_suppression [220, 270]
                          infection.mutation_evaluation.mutation.heuristic [230, 240]
                          infection.mutation_evaluation.mutation.heuristic [250, 260]
                        infection.mutation_evaluation.mutant_analysis [280, 470]
                          infection.mutation_evaluation.mutant_analysis.materialisation [290, 300]
                          infection.mutation_evaluation.mutant_analysis.evaluation [410, 460]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [420, 430]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [440, 450]
                      infection.mutation_evaluation.mutation [310, 560]
                        infection.mutation_evaluation.mutation.heuristic_suppression [320, 370]
                          infection.mutation_evaluation.mutation.heuristic [330, 340]
                          infection.mutation_evaluation.mutation.heuristic [350, 360]
                        infection.mutation_evaluation.mutant_analysis [380, 550]
                          infection.mutation_evaluation.mutant_analysis.materialisation [390, 400]
                          infection.mutation_evaluation.mutant_analysis.evaluation [490, 540]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [500, 510]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [520, 530]
                  infection.reporting [570, 620]
                    infection.reporting.reporter [580, 590]
                    infection.reporting.reporter [600, 610]
                TXT,
        ];

        yield 'complete no-progress cycle with two basic mutations' => [
            [
                new ApplicationExecutionWasStarted(),
                new ArtefactCollectionWasStarted(),
                new InitialTestSuiteWasStarted(),
                new InitialTestSuiteWasFinished('Test suite output'),
                new InitialStaticAnalysisRunWasStarted(),
                new InitialStaticAnalysisRunWasFinished('Static analysis output'),
                new ArtefactCollectionWasFinished(),
                new SourceCollectionWasStarted(),
                new SourceCollectionWasFinished(2),
                new MutationAnalysisWasStarted(),
                new MutationEvaluationWasStarted(2, new NullProcessRunner()),
                new MutationGenerationWasStarted(2),
                new AstProcessingWasStarted('/path/to/project/src/Foo.php'),
                new AstParsingWasStarted('/path/to/project/src/Foo.php'),
                new AstParsingWasFinished('/path/to/project/src/Foo.php'),
                new AstEnrichmentWasStarted('/path/to/project/src/Foo.php'),
                new AstEnrichmentWasFinished('/path/to/project/src/Foo.php'),
                new AstProcessingWasFinished('/path/to/project/src/Foo.php'),
                new MutationEvaluationForMutationWasStarted($mutationA),
                new HeuristicSuppressionWasStarted($mutationA),
                new HeuristicWasStarted($mutationA, HeuristicName::IGNORED_BY_REGEX),
                new HeuristicWasFinished($mutationA, HeuristicName::IGNORED_BY_REGEX),
                new HeuristicWasStarted($mutationA, HeuristicName::UNCOVERED_BY_TESTS),
                new HeuristicWasFinished($mutationA, HeuristicName::UNCOVERED_BY_TESTS),
                new HeuristicWasStarted($mutationA, HeuristicName::TAKING_TOO_LONG),
                new HeuristicWasFinished($mutationA, HeuristicName::TAKING_TOO_LONG),
                new HeuristicSuppressionWasFinished($mutationA),
                new MutantAnalysisWasStarted($mutantA),
                new MutantMaterialisationWasStarted($mutantA),
                new MutantMaterialisationWasFinished($mutantA),
                new AstProcessingWasStarted('/path/to/project/src/Bar.php'),
                new AstParsingWasStarted('/path/to/project/src/Bar.php'),
                new AstParsingWasFinished('/path/to/project/src/Bar.php'),
                new AstEnrichmentWasStarted('/path/to/project/src/Bar.php'),
                new AstEnrichmentWasFinished('/path/to/project/src/Bar.php'),
                new AstProcessingWasFinished('/path/to/project/src/Bar.php'),
                new MutationEvaluationForMutationWasStarted($mutationB),
                new HeuristicSuppressionWasStarted($mutationB),
                new HeuristicWasStarted($mutationB, HeuristicName::IGNORED_BY_REGEX),
                new HeuristicWasFinished($mutationB, HeuristicName::IGNORED_BY_REGEX),
                new HeuristicWasStarted($mutationB, HeuristicName::UNCOVERED_BY_TESTS),
                new HeuristicWasFinished($mutationB, HeuristicName::UNCOVERED_BY_TESTS),
                new HeuristicWasStarted($mutationB, HeuristicName::TAKING_TOO_LONG),
                new HeuristicWasFinished($mutationB, HeuristicName::TAKING_TOO_LONG),
                new HeuristicSuppressionWasFinished($mutationB),
                new MutantAnalysisWasStarted($mutantB),
                new MutantMaterialisationWasStarted($mutantB),
                new MutantMaterialisationWasFinished($mutantB),
                new MutationGenerationWasFinished(2, 2),
                new MutantEvaluationWasStarted($mutantA),
                new MutantProcessExecutionWasStarted($noProgressMutationAProcess1, 1),
                new MutantProcessExecutionWasFinished($noProgressMutationAProcess1),
                new MutantProcessExecutionWasStarted($noProgressMutationAProcess2, 2),
                new MutantProcessExecutionWasFinished($noProgressMutationAProcess2),
                new MutantEvaluationWasFinished($mutantA),
                new MutantAnalysisWasFinished($mutantA),
                new MutationEvaluationForMutationWasFinished(
                    MutantExecutionResultBuilder::withMinimalTestData()
                        ->withMutantHash('mutation-A')
                        ->build(),
                ),
                new MutantEvaluationWasStarted($mutantB),
                new MutantProcessExecutionWasStarted($noProgressMutationBProcess1, 1),
                new MutantProcessExecutionWasFinished($noProgressMutationBProcess1),
                new MutantProcessExecutionWasStarted($noProgressMutationBProcess2, 2),
                new MutantProcessExecutionWasFinished($noProgressMutationBProcess2),
                new MutantEvaluationWasFinished($mutantB),
                new MutantAnalysisWasFinished($mutantB),
                new MutationEvaluationForMutationWasFinished(
                    MutantExecutionResultBuilder::withMinimalTestData()
                        ->withMutantHash('mutation-B')
                        ->build(),
                ),
                new ReportingWasStarted(),
                new ReporterWasStarted(123, ReporterName::FILE_REPORTERS),
                new ReporterWasFinished(123, ReporterName::FILE_REPORTERS),
                new ReporterWasStarted(456, ReporterName::SHOW_METRICS),
                new ReporterWasFinished(456, ReporterName::SHOW_METRICS),
                new ReporterWasStarted(789, ReporterName::ADVISORY),
                new ReporterWasFinished(789, ReporterName::ADVISORY),
                new ReportingWasFinished(),
                new MutationEvaluationWasFinished(),
                new MutationAnalysisWasFinished(),
                new ApplicationExecutionWasFinished(),
            ],
            <<<'TXT'
                infection.run [10, 780]
                  infection.artefact_collection [20, 70]
                    infection.initial_tests [30, 40]
                    infection.initial_static_analysis [50, 60]
                  infection.source_collection [80, 90]
                  infection.mutation_analysis [100, 770]
                    infection.mutation_evaluation [110, 750]
                      infection.mutation_evaluation.mutation [200, 580]
                        infection.mutation_evaluation.mutation.heuristic_suppression [210, 280]
                          infection.mutation_evaluation.mutation.heuristic [220, 230]
                          infection.mutation_evaluation.mutation.heuristic [240, 250]
                          infection.mutation_evaluation.mutation.heuristic [260, 270]
                        infection.mutation_evaluation.mutant_analysis [290, 570]
                          infection.mutation_evaluation.mutant_analysis.materialisation [300, 310]
                          infection.mutation_evaluation.mutant_analysis.evaluation [510, 560]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [520, 530]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [540, 550]
                      infection.mutation_evaluation.mutation [380, 660]
                        infection.mutation_evaluation.mutation.heuristic_suppression [390, 460]
                          infection.mutation_evaluation.mutation.heuristic [400, 410]
                          infection.mutation_evaluation.mutation.heuristic [420, 430]
                          infection.mutation_evaluation.mutation.heuristic [440, 450]
                        infection.mutation_evaluation.mutant_analysis [470, 650]
                          infection.mutation_evaluation.mutant_analysis.materialisation [480, 490]
                          infection.mutation_evaluation.mutant_analysis.evaluation [590, 640]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [600, 610]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [620, 630]
                    infection.mutation_generation [120, 500]
                    infection.ast_processing [130, 760]
                      infection.ast_processing.file [140, 190]
                        infection.ast_processing.file.parsing [150, 160]
                        infection.ast_processing.file.enrichment [170, 180]
                      infection.ast_processing.file [320, 370]
                        infection.ast_processing.file.parsing [330, 340]
                        infection.ast_processing.file.enrichment [350, 360]
                  infection.reporting [670, 740]
                    infection.reporting.reporter [680, 690]
                    infection.reporting.reporter [700, 710]
                    infection.reporting.reporter [720, 730]
                TXT,
        ];
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

        $this->exporter->assertAllSpansAreFinished();
        $this->guardedTracer->assertHasNoOpenSpans();
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

        $this->exporter->assertAllSpansAreFinished();
    }

    public function test_it_calculates_the_queue_wait_duration_for_each_mutant_evaluation(): void
    {
        $mutationA = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-A')
            ->build();
        $mutationB = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-B')
            ->build();
        $mutantA = MutantBuilder::withMinimalTestData()
            ->withMutation($mutationA)
            ->build();
        $mutantB = MutantBuilder::withMinimalTestData()
            ->withMutation($mutationB)
            ->build();
        $mutationAProcess0 = $this->createMutantProcess($mutantA, 0);
        $mutationAProcess1 = $this->createMutantProcess($mutantA, 0);
        $mutationBProcess0 = $this->createMutantProcess($mutantB, 0);
        $mutationBProcess1 = $this->createMutantProcess($mutantB, 0);

        $this->subscriber->onApplicationExecutionWasStarted(new ApplicationExecutionWasStarted());
        $this->subscriber->onMutationAnalysisWasStarted(new MutationAnalysisWasStarted());
        $this->subscriber->onMutationEvaluationWasStarted(new MutationEvaluationWasStarted(2, $this->createStub(ProcessRunner::class)));

        $this->clock->setTime(2_000_000_000);
        $this->subscriber->onMutantEvaluationWasStarted(new MutantEvaluationWasStarted($mutantA));
        $this->clock->setTime(2_000_000_010);
        $this->subscriber->onMutantProcessExecutionWasStarted(new MutantProcessExecutionWasStarted($mutationAProcess0, 1));
        $this->clock->setTime(2_000_000_030);
        $this->subscriber->onMutantProcessExecutionWasFinished(new MutantProcessExecutionWasFinished($mutationAProcess0));
        $this->clock->setTime(2_000_000_070);
        $this->subscriber->onMutantProcessExecutionWasStarted(new MutantProcessExecutionWasStarted($mutationAProcess1, 1));
        $this->clock->setTime(2_000_000_090);
        $this->subscriber->onMutantProcessExecutionWasFinished(new MutantProcessExecutionWasFinished($mutationAProcess1));
        $this->clock->setTime(2_000_000_110);
        $this->subscriber->onMutantEvaluationWasFinished(new MutantEvaluationWasFinished($mutantA));

        $this->clock->setTime(3_000_000_000);
        $this->subscriber->onMutantEvaluationWasStarted(new MutantEvaluationWasStarted($mutantB));
        $this->clock->setTime(3_000_000_025);
        $this->subscriber->onMutantProcessExecutionWasStarted(new MutantProcessExecutionWasStarted($mutationBProcess0, 2));
        $this->clock->setTime(3_000_000_055);
        $this->subscriber->onMutantProcessExecutionWasFinished(new MutantProcessExecutionWasFinished($mutationBProcess0));
        $this->clock->setTime(3_000_000_115);
        $this->subscriber->onMutantProcessExecutionWasStarted(new MutantProcessExecutionWasStarted($mutationBProcess1, 2));
        $this->clock->setTime(3_000_000_145);
        $this->subscriber->onMutantProcessExecutionWasFinished(new MutantProcessExecutionWasFinished($mutationBProcess1));
        $this->clock->setTime(3_000_000_200);
        $this->subscriber->onMutantEvaluationWasFinished(new MutantEvaluationWasFinished($mutantB));

        $this->assertSame(
            0.000000050,
            $this->getMutantEvaluationSpanForMutation('mutation-A')->getAttributes()->get('infection.mutation.queue_wait.duration'),
        );
        $this->assertSame(
            0.000000085,
            $this->getMutantEvaluationSpanForMutation('mutation-B')->getAttributes()->get('infection.mutation.queue_wait.duration'),
        );
    }

    private function createSubscriber(ClockInterface $clock): OpenTelemetryTracerSubscriber
    {
        $configuration = ConfigurationBuilder::withMinimalTestData()
            ->withProjectDirectory('/path/to/project')
            ->build();

        $testFrameworkAdapter = $this->createStub(TestFrameworkAdapter::class);
        $testFrameworkAdapter
            ->method('getVersion')
            ->willReturn('12.3.4');
        $projectRelativePathResolver = new ProjectRelativePathResolver($configuration);

        $this->guardedTracer = new GuardedTracer(
            $this->tracerProvider->getTracer('infection'),
        );

        return new OpenTelemetryTracerSubscriber(
            new OpenTelemetryTracer(
                $this->guardedTracer,
                $this->tracerProvider,
                $clock,
            ),
            new RunSpanAttributesProvider(
                $configuration,
                new InfectionVersion(),
                $testFrameworkAdapter,
                null,
                $this->metricsCalculator,
            ),
            new MutationSpanAttributesProvider(
                $projectRelativePathResolver,
                $configuration->timeoutsAsEscaped,
            ),
            $projectRelativePathResolver,
            $configuration->testFramework,
        );
    }

    /**
     * @param list<object> $events
     */
    private function recordEvents(
        OpenTelemetryTracerSubscriber $subscriber,
        array $events,
    ): void {
        $dispatcher = new SyncEventDispatcher();
        $dispatcher->addSubscriber($subscriber);

        foreach ($events as $event) {
            $dispatcher->dispatch($event);
        }
    }

    private static function createFinishedMutantProcess(Mutant $mutant): MutantProcess
    {
        return new MutantProcess(
            new Process(['php', '-v']),
            $mutant,
            new DummyMutantExecutionResultFactory(),
        );
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

    private function getMutantProcessExecutionSpanForThread(int $thread): SpanDataInterface
    {
        /** @var SpanDataInterface $span */
        foreach ($this->exporter->getSpans() as $span) {
            if (
                $span->getName() === 'infection.mutation_evaluation.mutant_analysis.evaluation.process'
                && $span->getAttributes()->get('infection.mutation.process.thread') === $thread
            ) {
                return $span;
            }
        }

        $this->fail(
            sprintf(
                'Mutant process execution span for thread "%d" was not exported.',
                $thread,
            ),
        );
    }

    private function createMutantProcess(
        Mutant $mutant,
        ?int $exitCode,
        bool $timedOut = false,
    ): MutantProcess {
        $process = $this->createMock(Process::class);
        $process
            ->method('getExitCode')
            ->willReturn($exitCode);

        $mutantProcess = $this->createMock(MutantProcess::class);
        $mutantProcess
            ->method('getMutant')
            ->willReturn($mutant);
        $mutantProcess
            ->method('getProcess')
            ->willReturn($process);
        $mutantProcess
            ->method('isTimedOut')
            ->willReturn($timedOut);

        return $mutantProcess;
    }

    private function getMutantEvaluationSpanForMutation(string $mutationHash): SpanDataInterface
    {
        /** @var SpanDataInterface $span */
        foreach ($this->exporter->getSpans() as $span) {
            if (
                $span->getName() === 'infection.mutation_evaluation.mutant_analysis.evaluation'
                && $span->getAttributes()->get('infection.mutation.id') === $mutationHash
            ) {
                return $span;
            }
        }

        $this->fail(
            sprintf(
                'Mutant evaluation span for mutation "%s" was not exported.',
                $mutationHash,
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

    /**
     * @param class-string $startEventClass
     * @param class-string $endEventClass
     */
    private function assertSpanEventClasses(SpanDataInterface $span, string $startEventClass, string $endEventClass): void
    {
        $this->assertSame($startEventClass, $span->getAttributes()->get('infection.event.class.start'));
        $this->assertSame($endEventClass, $span->getAttributes()->get('infection.event.class.end'));
    }

    private function assertTracerProviderWasShutdown(): void
    {
        $this->assertFalse($this->tracerProvider->getTracer('infection')->isEnabled());
    }
}
