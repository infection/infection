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

use function array_filter;
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

/**
 * @phpstan-import-type Attributes from RunSpanAttributesProvider
 */
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
        $process1 = $this->createMutantProcess($mutant, 1, timedOut: true);

        $executionResult = MutantExecutionResultBuilder::withMinimalTestData()
            ->withMutantHash($mutation->getHash())
            ->withDetectionStatus(DetectionStatus::KILLED_BY_TESTS)
            ->withProcessRuntime(0.123)
            ->build();
        $this->metricsCalculator->collect($executionResult);
        $fakeReporter = new FakeReporter();
        $reporterId = spl_object_id($fakeReporter);
        $reporterName = ReporterName::FILE_REPORTERS;

        $this->recordEvents(
            $this->subscriber,
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
                new AstProcessingWasStarted('/path/to/project/src/Foo.php'),
                new AstParsingWasStarted('/path/to/project/src/Foo.php'),
                new AstParsingWasFinished('/path/to/project/src/Foo.php'),
                new AstEnrichmentWasStarted('/path/to/project/src/Foo.php'),
                new AstEnrichmentWasFinished('/path/to/project/src/Foo.php'),
                new AstProcessingWasFinished('/path/to/project/src/Foo.php'),
                new MutationGenerationWasStarted(1),
                new MutationGenerationWasFinished(2, 1),
                new MutationEvaluationWasStarted(
                    1,
                    $this->createStub(ProcessRunner::class),
                ),
                new MutationEvaluationForMutationWasStarted($mutation),
                new HeuristicSuppressionWasStarted($mutation),
                new HeuristicWasStarted($mutation, HeuristicName::IGNORED_BY_REGEX),
                new HeuristicWasFinished($mutation, HeuristicName::IGNORED_BY_REGEX),
                new HeuristicWasStarted($mutation, HeuristicName::UNCOVERED_BY_TESTS),
                new HeuristicWasFinished($mutation, HeuristicName::UNCOVERED_BY_TESTS),
                new HeuristicSuppressionWasFinished($mutation),
                new MutantAnalysisWasStarted($mutant),
                new MutantMaterialisationWasStarted($mutant),
                new MutantMaterialisationWasFinished($mutant),
                new SetClockAt(2_000_000_000),
                new MutantEvaluationWasStarted($mutant),
                new SetClockAt(2_000_000_010),
                new MutantProcessExecutionWasStarted($process0, 1),
                new MutantProcessExecutionWasFinished($process0),
                new MutantProcessExecutionWasStarted($process1, 2),
                new MutantProcessExecutionWasFinished($process1),
                new MutantEvaluationWasFinished($mutant),
                new MutantAnalysisWasFinished($mutant),
                new MutationEvaluationForMutationWasFinished($executionResult),
                new ReportingWasStarted(),
                new ReporterWasStarted($reporterId, $reporterName),
                new ReporterWasFinished($reporterId, $reporterName),
                new ReportingWasFinished(),
                new MutationEvaluationWasFinished(),
                new MutationAnalysisWasFinished(),
                new ApplicationExecutionWasFinished(),
            ],
        );

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
            $this->exporter->getSpanNames(),
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
        $heuristic = $this->getHeuristicSpanForHeuristic(HeuristicName::IGNORED_BY_REGEX);
        $secondHeuristic = $this->getHeuristicSpanForHeuristic(HeuristicName::UNCOVERED_BY_TESTS);
        $heuristicSuppression = $this->getSpanFromExporter('infection.mutation_evaluation.mutation.heuristic_suppression');
        $mutantAnalysis = $this->getSpanFromExporter('infection.mutation_evaluation.mutant_analysis');
        $mutantMaterialisation = $this->getSpanFromExporter('infection.mutation_evaluation.mutant_analysis.materialisation');
        $mutantEvaluation = $this->getSpanFromExporter('infection.mutation_evaluation.mutant_analysis.evaluation');
        $process = $this->getMutantProcessExecutionSpanForThread(1);
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

        $mutationAttributes = [
            'infection.mutation.id' => 'mutation-A',
            'infection.mutator.name' => 'For_',
            'code.file.path' => 'src/Foo.php',
            'code.line.start' => 10,
            'code.line.end' => 15,
        ];

        $this->assertSpanAttributesEquals(
            self::createAttributes(
                ApplicationExecutionWasStarted::class,
                ApplicationExecutionWasFinished::class,
                [
                    'infection.project.name' => 'project',
                    'infection.project.path' => '/path/to/project',
                    'infection.config.path' => 'infection.json5',
                    'infection.version' => (new InfectionVersion())->prettyVersion(),
                    'infection.distribution' => 'source',
                    'infection.thread.count' => 1,
                    'infection.run.source_filtered' => false,
                    'infection.timeouts_as_escaped' => false,
                    'infection.initial_tests.skipped' => false,
                    'infection.initial_static_analysis.skipped' => true,
                    'infection.test_framework.name' => 'phpunit',
                    'infection.test_framework.version' => '12.3.4',
                    'infection.source_file.count' => 1,
                    'infection.mutated_file.count' => 1,
                    'infection.mutation.generated.count' => 2,
                    'infection.mutation.evaluated.count' => 1,
                    'infection.mutation.suppressed.count' => 1,
                    'infection.mutation.eligible.count' => 1,
                    'infection.mutation.ineligible.count' => 0,
                    'infection.mutation.tested_eligible.count' => 1,
                    'infection.mutation.covered.count' => 1,
                    'infection.mutation.tested_not_covered.count' => 0,
                    'infection.mutation.not_covered.count' => 0,
                    'infection.mutation.not_tested.count' => 0,
                    'infection.mutation.killed_by_tests.count' => 1,
                    'infection.mutation.killed_by_static_analysis.count' => 0,
                    'infection.mutation.escaped.count' => 0,
                    'infection.mutation.error.count' => 0,
                    'infection.mutation.timed_out.count' => 0,
                    'infection.mutation.skipped.count' => 0,
                    'infection.mutation.syntax_error.count' => 0,
                    'infection.mutation.ignored.count' => 0,
                    'infection.msi.value' => 100.0,
                    'infection.mutation.coverage_rate.value' => 100.0,
                    'infection.covered_msi.value' => 100.0,
                    'infection.msi.threshold' => 0.0,
                    'infection.covered_msi.threshold' => 0.0,
                ],
            ),
            $run,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(ArtefactCollectionWasStarted::class, ArtefactCollectionWasFinished::class),
            $artefactCollection,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(SourceCollectionWasStarted::class, SourceCollectionWasFinished::class, ['infection.source_file.count' => 1]),
            $sourceCollection,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(InitialTestSuiteWasStarted::class, InitialTestSuiteWasFinished::class),
            $initialTests,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(InitialStaticAnalysisRunWasStarted::class, InitialStaticAnalysisRunWasFinished::class),
            $initialStaticAnalysis,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(MutationAnalysisWasStarted::class, MutationAnalysisWasFinished::class),
            $mutationAnalysis,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(AstProcessingWasStarted::class, MutationGenerationWasStarted::class),
            $astProcessing,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(AstProcessingWasStarted::class, AstProcessingWasFinished::class, ['code.file.path' => 'src/Foo.php']),
            $astProcessingFile,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(AstParsingWasStarted::class, AstParsingWasFinished::class, ['code.file.path' => 'src/Foo.php']),
            $astParsing,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(AstEnrichmentWasStarted::class, AstEnrichmentWasFinished::class, ['code.file.path' => 'src/Foo.php']),
            $astEnrichment,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(
                MutationGenerationWasStarted::class,
                MutationGenerationWasFinished::class,
                [
                    'infection.source_file.count' => 1,
                    'infection.mutated_file.count' => 1,
                    'infection.mutation.generated.count' => 2,
                ],
            ),
            $mutationGeneration,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(MutationEvaluationWasStarted::class, MutationEvaluationWasFinished::class),
            $mutationEvaluation,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(
                MutationEvaluationForMutationWasStarted::class,
                MutationEvaluationForMutationWasFinished::class,
                [
                    ...$mutationAttributes,
                    'infection.mutation.status' => DetectionStatus::KILLED_BY_TESTS->value,
                    'infection.mutation.runtime' => 0.123,
                    'infection.mutation.msi.category' => 'covered',
                ],
            ),
            $mutationEvaluationForMutation,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(
                HeuristicWasStarted::class,
                HeuristicWasFinished::class,
                [
                    ...$mutationAttributes,
                    'infection.mutation_evaluation.heuristic.id' => HeuristicName::IGNORED_BY_REGEX->value,
                ],
            ),
            $heuristic,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(
                HeuristicWasStarted::class,
                HeuristicWasFinished::class,
                [
                    ...$mutationAttributes,
                    'infection.mutation_evaluation.heuristic.id' => HeuristicName::UNCOVERED_BY_TESTS->value,
                ],
            ),
            $secondHeuristic,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(HeuristicSuppressionWasStarted::class, HeuristicSuppressionWasFinished::class, $mutationAttributes),
            $heuristicSuppression,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(MutantAnalysisWasStarted::class, MutantAnalysisWasFinished::class, $mutationAttributes),
            $mutantAnalysis,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(MutantMaterialisationWasStarted::class, MutantMaterialisationWasFinished::class, $mutationAttributes),
            $mutantMaterialisation,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(
                MutantEvaluationWasStarted::class,
                MutantEvaluationWasFinished::class,
                [
                    ...$mutationAttributes,
                    'infection.mutation.queue_wait.duration' => 0.000000010,
                ],
            ),
            $mutantEvaluation,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(
                MutantProcessExecutionWasStarted::class,
                MutantProcessExecutionWasFinished::class,
                [
                    ...$mutationAttributes,
                    'infection.mutation.process.test_framework' => 'phpunit',
                    'infection.mutation.process.thread' => 1,
                    'infection.mutation.process.timed_out' => false,
                    'process.exit.code' => 0,
                ],
            ),
            $process,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(
                MutantProcessExecutionWasStarted::class,
                MutantProcessExecutionWasFinished::class,
                [
                    ...$mutationAttributes,
                    'infection.mutation.process.test_framework' => 'phpunit',
                    'infection.mutation.process.thread' => 2,
                    'infection.mutation.process.timed_out' => true,
                    'process.exit.code' => 1,
                ],
            ),
            $secondProcess,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(ReportingWasStarted::class, ReportingWasFinished::class),
            $reporting,
        );
        $this->assertSpanAttributesEquals(
            self::createAttributes(
                ReporterWasStarted::class,
                ReporterWasFinished::class,
                [
                    'infection.reporter.id' => $reporterId,
                    'infection.reporter.name' => 'file',
                ],
            ),
            $reporter,
        );

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
                    infection.mutation_generation [110, 200]
                    infection.ast_processing [120, 190]
                      infection.ast_processing.file [130, 180]
                        infection.ast_processing.file.parsing [140, 150]
                        infection.ast_processing.file.enrichment [160, 170]
                    infection.mutation_evaluation [210, 640]
                      infection.mutation_evaluation.mutation [220, 490]
                        infection.mutation_evaluation.mutation.heuristic_suppression [230, 280]
                          infection.mutation_evaluation.mutation.heuristic [240, 250]
                          infection.mutation_evaluation.mutation.heuristic [260, 270]
                        infection.mutation_evaluation.mutant_analysis [290, 480]
                          infection.mutation_evaluation.mutant_analysis.materialisation [300, 310]
                          infection.mutation_evaluation.mutant_analysis.evaluation [420, 470]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [430, 440]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [450, 460]
                      infection.mutation_evaluation.mutation [320, 570]
                        infection.mutation_evaluation.mutation.heuristic_suppression [330, 380]
                          infection.mutation_evaluation.mutation.heuristic [340, 350]
                          infection.mutation_evaluation.mutation.heuristic [360, 370]
                        infection.mutation_evaluation.mutant_analysis [390, 560]
                          infection.mutation_evaluation.mutant_analysis.materialisation [400, 410]
                          infection.mutation_evaluation.mutant_analysis.evaluation [500, 550]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [510, 520]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [530, 540]
                  infection.reporting [580, 630]
                    infection.reporting.reporter [590, 600]
                    infection.reporting.reporter [610, 620]
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
                    infection.mutation_evaluation [110, 760]
                      infection.mutation_evaluation.mutation [200, 590]
                        infection.mutation_evaluation.mutation.heuristic_suppression [210, 280]
                          infection.mutation_evaluation.mutation.heuristic [220, 230]
                          infection.mutation_evaluation.mutation.heuristic [240, 250]
                          infection.mutation_evaluation.mutation.heuristic [260, 270]
                        infection.mutation_evaluation.mutant_analysis [290, 580]
                          infection.mutation_evaluation.mutant_analysis.materialisation [300, 310]
                          infection.mutation_evaluation.mutant_analysis.evaluation [520, 570]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [530, 540]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [550, 560]
                      infection.mutation_evaluation.mutation [390, 670]
                        infection.mutation_evaluation.mutation.heuristic_suppression [400, 470]
                          infection.mutation_evaluation.mutation.heuristic [410, 420]
                          infection.mutation_evaluation.mutation.heuristic [430, 440]
                          infection.mutation_evaluation.mutation.heuristic [450, 460]
                        infection.mutation_evaluation.mutant_analysis [480, 660]
                          infection.mutation_evaluation.mutant_analysis.materialisation [490, 500]
                          infection.mutation_evaluation.mutant_analysis.evaluation [600, 650]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [610, 620]
                            infection.mutation_evaluation.mutant_analysis.evaluation.process [630, 640]
                    infection.mutation_generation [120, 510]
                    infection.ast_processing [130, 380]
                      infection.ast_processing.file [140, 190]
                        infection.ast_processing.file.parsing [150, 160]
                        infection.ast_processing.file.enrichment [170, 180]
                      infection.ast_processing.file [320, 370]
                        infection.ast_processing.file.parsing [330, 340]
                        infection.ast_processing.file.enrichment [350, 360]
                  infection.reporting [680, 750]
                    infection.reporting.reporter [690, 700]
                    infection.reporting.reporter [710, 720]
                    infection.reporting.reporter [730, 740]
                TXT,
        ];
    }

    public function test_it_ends_open_spans_on_application_finish_even_if_the_finish_events_were_not_emitted(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()->build();

        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new ArtefactCollectionWasStarted(),
                new InitialTestSuiteWasStarted(),
                new InitialStaticAnalysisRunWasStarted(),
                new SourceCollectionWasStarted(),
                new MutationAnalysisWasStarted(),
                new AstProcessingWasStarted('/path/to/project/src/Foo.php'),
                new AstParsingWasStarted('/path/to/project/src/Foo.php'),
                new AstEnrichmentWasStarted('/path/to/project/src/Foo.php'),
                new MutationGenerationWasStarted(1),
                new MutationEvaluationWasStarted(IterableCounter::UNKNOWN_COUNT, $this->createStub(ProcessRunner::class)),
                new MutationEvaluationForMutationWasStarted($mutation),
                new ReportingWasStarted(),
                new ReporterWasStarted(123, ReporterName::FILE_REPORTERS),
                new ApplicationExecutionWasFinished(),
            ],
        );

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
                'infection.mutation_evaluation.mutation',
                'infection.mutation_evaluation',
                'infection.mutation_analysis',
                'infection.reporting.reporter',
                'infection.reporting',
                'infection.run',
            ],
            $this->exporter->getSpanNames(),
        );

        $this->exporter->assertAllSpansAreFinished();
        $this->guardedTracer->assertHasNoOpenSpans();
        $this->assertTracerProviderWasShutdown();
    }

    public function test_it_ends_open_mutation_evaluation_for_mutation_spans_on_mutation_evaluation_finish(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()->build();

        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new MutationEvaluationWasStarted(IterableCounter::UNKNOWN_COUNT, $this->createStub(ProcessRunner::class)),
                new MutationEvaluationForMutationWasStarted($mutation),
                new MutationEvaluationWasFinished(),
            ],
        );

        $this->assertSame(
            [
                'infection.mutation_evaluation.mutation',
                'infection.mutation_evaluation',
            ],
            $this->exporter->getSpanNames(),
        );

        $this->exporter->assertAllSpansAreFinished();
    }

    public function test_it_ends_open_mutation_evaluation_child_spans_on_mutation_evaluation_finish(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-A')
            ->build();
        $mutant = MutantBuilder::withMinimalTestData()
            ->withMutation($mutation)
            ->build();
        $process = $this->createMutantProcess($mutant, 0);

        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new MutationEvaluationWasStarted(1, $this->createStub(ProcessRunner::class)),
                new MutationEvaluationForMutationWasStarted($mutation),
                new HeuristicSuppressionWasStarted($mutation),
                new HeuristicWasStarted($mutation, HeuristicName::IGNORED_BY_REGEX),
                new MutantAnalysisWasStarted($mutant),
                new MutantMaterialisationWasStarted($mutant),
                new MutantEvaluationWasStarted($mutant),
                new MutantProcessExecutionWasStarted($process, 1),
                new MutationEvaluationWasFinished(),
            ],
        );

        $this->assertSame(
            [
                'infection.mutation_evaluation.mutation.heuristic',
                'infection.mutation_evaluation.mutation.heuristic_suppression',
                'infection.mutation_evaluation.mutant_analysis.materialisation',
                'infection.mutation_evaluation.mutant_analysis.evaluation.process',
                'infection.mutation_evaluation.mutant_analysis.evaluation',
                'infection.mutation_evaluation.mutant_analysis',
                'infection.mutation_evaluation.mutation',
                'infection.mutation_evaluation',
            ],
            $this->exporter->getSpanNames(),
        );

        foreach ($this->exporter->getSpans() as $span) {
            $this->assertSame(MutationEvaluationWasFinished::class, $span->getAttributes()->get('infection.event.class.end'));
        }
    }

    public function test_it_ends_open_ast_spans_on_application_finish(): void
    {
        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new AstProcessingWasStarted('/path/to/project/src/Foo.php'),
                new AstParsingWasStarted('/path/to/project/src/Foo.php'),
                new AstEnrichmentWasStarted('/path/to/project/src/Foo.php'),
                new ApplicationExecutionWasFinished(),
            ],
        );

        $this->assertSame(
            [
                'infection.ast_processing.file.parsing',
                'infection.ast_processing.file.enrichment',
                'infection.ast_processing.file',
                'infection.ast_processing',
                'infection.mutation_analysis',
                'infection.run',
            ],
            $this->exporter->getSpanNames(),
        );
        $this->assertSpanEventClasses(
            $this->getSpanFromExporter('infection.ast_processing'),
            AstProcessingWasStarted::class,
            ApplicationExecutionWasFinished::class,
        );
    }

    public function test_it_ends_open_ast_spans_on_mutation_analysis_finish(): void
    {
        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new AstProcessingWasStarted('/path/to/project/src/Foo.php'),
                new AstParsingWasStarted('/path/to/project/src/Foo.php'),
                new AstEnrichmentWasStarted('/path/to/project/src/Foo.php'),
                new MutationAnalysisWasFinished(),
                new ApplicationExecutionWasFinished(),
            ],
        );

        $this->assertSame(
            [
                'infection.ast_processing.file.parsing',
                'infection.ast_processing.file.enrichment',
                'infection.ast_processing.file',
                'infection.ast_processing',
                'infection.mutation_analysis',
                'infection.run',
            ],
            $this->exporter->getSpanNames(),
        );
        $this->assertSpanEventClasses(
            $this->getSpanFromExporter('infection.ast_processing'),
            AstProcessingWasStarted::class,
            MutationAnalysisWasFinished::class,
        );
    }

    public function test_it_ends_the_ast_processing_span_when_mutation_generation_starts_with_no_mutable_files(): void
    {
        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new AstProcessingWasStarted('/path/to/project/src/Foo.php'),
                new AstProcessingWasFinished('/path/to/project/src/Foo.php'),
                new MutationGenerationWasStarted(0),
                new ApplicationExecutionWasFinished(),
            ],
        );

        $this->assertSpanEventClasses(
            $this->getSpanFromExporter('infection.ast_processing'),
            AstProcessingWasStarted::class,
            MutationGenerationWasStarted::class,
        );
    }

    public function test_it_waits_for_all_mutable_files_before_ending_the_ast_processing_span(): void
    {
        $this->recordEvents(
            $this->subscriber,
            [
                new SetClockAt(1000),
                new ApplicationExecutionWasStarted(),
                new SetClockAt(1100),
                new MutationAnalysisWasStarted(),
                new SetClockAt(1200),
                new MutationGenerationWasStarted(2),
                new SetClockAt(1300),
                new AstProcessingWasStarted('/path/to/project/src/Foo.php'),
                new SetClockAt(1400),
                new AstProcessingWasFinished('/path/to/project/src/Foo.php'),
                new SetClockAt(1500),
                new AstProcessingWasStarted('/path/to/project/src/Bar.php'),
                new SetClockAt(1600),
                new AstProcessingWasFinished('/path/to/project/src/Bar.php'),
                new SetClockAt(1700),
                new ApplicationExecutionWasFinished(),
            ],
        );

        $this->assertSame(
            <<<'TXT'
                infection.run [1000, 1700]
                  infection.mutation_analysis [1100, 1700]
                    infection.mutation_generation [1200, 1700]
                    infection.ast_processing [1300, 1600]
                      infection.ast_processing.file [1300, 1400]
                      infection.ast_processing.file [1500, 1600]
                TXT,
            SpanTreeRenderer::render($this->exporter->getSpans()),
        );
        $this->assertSpanEventClasses(
            $this->getSpanFromExporter('infection.ast_processing'),
            AstProcessingWasStarted::class,
            AstProcessingWasFinished::class,
        );
    }

    public function test_it_ends_open_mutation_evaluation_child_spans_on_application_finish(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-A')
            ->build();
        $mutant = MutantBuilder::withMinimalTestData()
            ->withMutation($mutation)
            ->build();

        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new MutationEvaluationWasStarted(1, $this->createStub(ProcessRunner::class)),
                new MutationEvaluationForMutationWasStarted($mutation),
                new MutantAnalysisWasStarted($mutant),
                new MutantEvaluationWasStarted($mutant),
                new ApplicationExecutionWasFinished(),
            ],
        );

        $this->assertSame(
            [
                'infection.mutation_evaluation.mutant_analysis.evaluation',
                'infection.mutation_evaluation.mutant_analysis',
                'infection.mutation_evaluation.mutation',
                'infection.mutation_evaluation',
                'infection.mutation_analysis',
                'infection.run',
            ],
            $this->exporter->getSpanNames(),
        );
        $this->assertSpanEventClasses(
            $this->getMutantEvaluationSpanForMutation('mutation-A'),
            MutantEvaluationWasStarted::class,
            ApplicationExecutionWasFinished::class,
        );
    }

    public function test_it_ends_open_mutant_materialisation_span_on_mutant_analysis_finish(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-A')
            ->build();
        $mutant = MutantBuilder::withMinimalTestData()
            ->withMutation($mutation)
            ->build();

        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new MutationEvaluationWasStarted(1, $this->createStub(ProcessRunner::class)),
                new MutationEvaluationForMutationWasStarted($mutation),
                new MutantAnalysisWasStarted($mutant),
                new MutantMaterialisationWasStarted($mutant),
                new MutantAnalysisWasFinished($mutant),
            ],
        );

        $this->assertSame(
            [
                'infection.mutation_evaluation.mutant_analysis.materialisation',
                'infection.mutation_evaluation.mutant_analysis',
            ],
            $this->exporter->getSpanNames(),
        );
        $this->assertSpanEventClasses(
            $this->getSpanFromExporter('infection.mutation_evaluation.mutant_analysis.materialisation'),
            MutantMaterialisationWasStarted::class,
            MutantAnalysisWasFinished::class,
        );
    }

    public function test_it_ends_open_mutation_analysis_child_spans_on_mutation_analysis_finish(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()->build();

        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new MutationGenerationWasStarted(1),
                new MutationEvaluationWasStarted(IterableCounter::UNKNOWN_COUNT, $this->createStub(ProcessRunner::class)),
                new MutationEvaluationForMutationWasStarted($mutation),
                new MutationAnalysisWasFinished(),
            ],
        );

        $this->assertSame(
            [
                'infection.mutation_generation',
                'infection.mutation_evaluation.mutation',
                'infection.mutation_evaluation',
                'infection.mutation_analysis',
            ],
            $this->exporter->getSpanNames(),
        );

        foreach ($this->exporter->getSpans() as $span) {
            $this->assertSame(MutationAnalysisWasFinished::class, $span->getAttributes()->get('infection.event.class.end'));
        }
    }

    public function test_it_ends_open_mutation_evaluation_child_spans_on_mutation_evaluation_for_mutation_finish(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-A')
            ->build();
        $mutant = MutantBuilder::withMinimalTestData()
            ->withMutation($mutation)
            ->build();
        $process = $this->createMutantProcess($mutant, 0);

        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new MutationEvaluationWasStarted(1, $this->createStub(ProcessRunner::class)),
                new MutationEvaluationForMutationWasStarted($mutation),
                new HeuristicSuppressionWasStarted($mutation),
                new HeuristicWasStarted($mutation, HeuristicName::IGNORED_BY_REGEX),
                new MutantAnalysisWasStarted($mutant),
                new MutantMaterialisationWasStarted($mutant),
                new MutantEvaluationWasStarted($mutant),
                new MutantProcessExecutionWasStarted($process, 1),
                new MutationEvaluationForMutationWasFinished(
                    MutantExecutionResultBuilder::withMinimalTestData()
                        ->withMutantHash('mutation-A')
                        ->build(),
                ),
            ],
        );

        $this->assertSame(
            [
                'infection.mutation_evaluation.mutation.heuristic',
                'infection.mutation_evaluation.mutation.heuristic_suppression',
                'infection.mutation_evaluation.mutant_analysis.materialisation',
                'infection.mutation_evaluation.mutant_analysis.evaluation.process',
                'infection.mutation_evaluation.mutant_analysis.evaluation',
                'infection.mutation_evaluation.mutant_analysis',
                'infection.mutation_evaluation.mutation',
            ],
            $this->exporter->getSpanNames(),
        );

        foreach ($this->exporter->getSpans() as $span) {
            $this->assertSame(MutationEvaluationForMutationWasFinished::class, $span->getAttributes()->get('infection.event.class.end'));
        }
    }

    public function test_it_ends_only_the_current_mutation_heuristic_spans_when_hashes_share_a_prefix(): void
    {
        $mutationA = MutationBuilder::withMinimalTestData()
            ->withHash('mutation')
            ->build();
        $mutationB = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-B')
            ->build();

        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new MutationEvaluationWasStarted(2, $this->createStub(ProcessRunner::class)),
                new MutationEvaluationForMutationWasStarted($mutationA),
                new MutationEvaluationForMutationWasStarted($mutationB),
                new HeuristicSuppressionWasStarted($mutationA),
                new HeuristicSuppressionWasStarted($mutationB),
                new HeuristicWasStarted($mutationA, HeuristicName::IGNORED_BY_REGEX),
                new HeuristicWasStarted($mutationB, HeuristicName::IGNORED_BY_REGEX),
                new HeuristicSuppressionWasFinished($mutationB),
            ],
        );

        $this->assertSame(
            [
                'mutation-B',
                'mutation-B',
            ],
            $this->getExportedMutationIds(),
        );

        $this->recordEvents(
            $this->subscriber,
            [
                new HeuristicSuppressionWasFinished($mutationA),
            ],
        );

        $this->assertSame(
            [
                'mutation-B',
                'mutation-B',
                'mutation',
                'mutation',
            ],
            $this->getExportedMutationIds(),
        );
    }

    public function test_it_does_not_end_heuristic_spans_for_a_different_mutation_with_a_matching_hash_prefix(): void
    {
        $mutationA = MutationBuilder::withMinimalTestData()
            ->withHash('mutation')
            ->build();
        $mutationB = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-B')
            ->build();

        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new MutationEvaluationWasStarted(2, $this->createStub(ProcessRunner::class)),
                new MutationEvaluationForMutationWasStarted($mutationA),
                new MutationEvaluationForMutationWasStarted($mutationB),
                new HeuristicSuppressionWasStarted($mutationB),
                new HeuristicSuppressionWasStarted($mutationA),
                new HeuristicWasStarted($mutationB, HeuristicName::IGNORED_BY_REGEX),
                new HeuristicWasStarted($mutationA, HeuristicName::IGNORED_BY_REGEX),
                new HeuristicSuppressionWasFinished($mutationA),
            ],
        );

        $this->assertSame(
            [
                'mutation',
                'mutation',
            ],
            $this->getExportedMutationIds(),
        );
    }

    public function test_it_tracks_overlapping_heuristics_for_the_same_mutation_separately(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-A')
            ->build();

        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new MutationEvaluationWasStarted(1, $this->createStub(ProcessRunner::class)),
                new MutationEvaluationForMutationWasStarted($mutation),
                new HeuristicSuppressionWasStarted($mutation),
                new HeuristicWasStarted($mutation, HeuristicName::IGNORED_BY_REGEX),
                new HeuristicWasStarted($mutation, HeuristicName::UNCOVERED_BY_TESTS),
                new HeuristicWasFinished($mutation, HeuristicName::IGNORED_BY_REGEX),
            ],
        );

        $this->assertSame(
            [HeuristicName::IGNORED_BY_REGEX->value],
            $this->getExportedHeuristicIds(),
        );

        $this->recordEvents(
            $this->subscriber,
            [
                new HeuristicWasFinished($mutation, HeuristicName::UNCOVERED_BY_TESTS),
            ],
        );

        $this->assertSame(
            [
                HeuristicName::IGNORED_BY_REGEX->value,
                HeuristicName::UNCOVERED_BY_TESTS->value,
            ],
            $this->getExportedHeuristicIds(),
        );
    }

    public function test_it_ends_open_mutant_process_spans_for_the_finished_mutant_evaluation_only(): void
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

        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new MutationEvaluationWasStarted(2, $this->createStub(ProcessRunner::class)),
                new MutationEvaluationForMutationWasStarted($mutationA),
                new MutationEvaluationForMutationWasStarted($mutationB),
                new MutantEvaluationWasStarted($mutantA),
                new MutantEvaluationWasStarted($mutantB),
                new MutantProcessExecutionWasStarted($this->createMutantProcess($mutantA, 0), 1),
                new MutantProcessExecutionWasStarted($this->createMutantProcess($mutantB, 0), 2),
                new MutantEvaluationWasFinished($mutantB),
            ],
        );

        $this->assertSame(
            [
                'mutation-B',
                'mutation-B',
            ],
            $this->getExportedMutationIds(),
        );

        $this->recordEvents(
            $this->subscriber,
            [
                new MutantEvaluationWasFinished($mutantA),
            ],
        );

        $this->assertSame(
            [
                'mutation-B',
                'mutation-B',
                'mutation-A',
                'mutation-A',
            ],
            $this->getExportedMutationIds(),
        );
    }

    public function test_it_ends_open_reporter_spans_on_reporting_finish(): void
    {
        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new ReportingWasStarted(),
                new ReporterWasStarted(123, ReporterName::FILE_REPORTERS),
                new ReportingWasFinished(),
            ],
        );

        $this->assertSame(
            [
                'infection.reporting.reporter',
                'infection.reporting',
            ],
            $this->exporter->getSpanNames(),
        );
        $this->assertSpanEventClasses(
            $this->getSpanFromExporter('infection.reporting.reporter'),
            ReporterWasStarted::class,
            ReportingWasFinished::class,
        );
    }

    public function test_it_does_not_record_queue_wait_duration_when_process_starts_without_a_mutant_evaluation(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-A')
            ->build();
        $mutant = MutantBuilder::withMinimalTestData()
            ->withMutation($mutation)
            ->build();
        $process = $this->createMutantProcess($mutant, 0);
        $secondProcess = $this->createMutantProcess($mutant, 0);

        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new MutationEvaluationWasStarted(1, $this->createStub(ProcessRunner::class)),
                new MutationEvaluationForMutationWasStarted($mutation),
                new MutantProcessExecutionWasStarted($process, 1),
                new MutantProcessExecutionWasFinished($process),
                new MutantProcessExecutionWasStarted($secondProcess, 1),
                new MutantProcessExecutionWasFinished($secondProcess),
                new MutantEvaluationWasStarted($mutant),
                new MutantEvaluationWasFinished($mutant),
            ],
        );

        $this->assertFalse(
            $this->getMutantEvaluationSpanForMutation('mutation-A')->getAttributes()->has('infection.mutation.queue_wait.duration'),
        );
    }

    public function test_it_records_zero_queue_wait_duration_when_process_starts_immediately(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()
            ->withHash('mutation-A')
            ->build();
        $mutant = MutantBuilder::withMinimalTestData()
            ->withMutation($mutation)
            ->build();
        $process = $this->createMutantProcess($mutant, 0);

        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new MutationEvaluationWasStarted(1, $this->createStub(ProcessRunner::class)),
                new SetClockAt(2_000_000_000),
                new MutantEvaluationWasStarted($mutant),
                new MutantProcessExecutionWasStarted($process, 1),
                new MutantProcessExecutionWasFinished($process),
                new MutantEvaluationWasFinished($mutant),
            ],
        );

        $this->assertSame(
            0,
            $this->getMutantEvaluationSpanForMutation('mutation-A')->getAttributes()->get('infection.mutation.queue_wait.duration'),
        );
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

        $this->recordEvents(
            $this->subscriber,
            [
                new ApplicationExecutionWasStarted(),
                new MutationAnalysisWasStarted(),
                new MutationEvaluationWasStarted(2, $this->createStub(ProcessRunner::class)),
                new SetClockAt(2_000_000_000),
                new MutantEvaluationWasStarted($mutantA),
                new SetClockAt(2_000_000_010),
                new MutantProcessExecutionWasStarted($mutationAProcess0, 1),
                new SetClockAt(2_000_000_030),
                new MutantProcessExecutionWasFinished($mutationAProcess0),
                new SetClockAt(2_000_000_070),
                new MutantProcessExecutionWasStarted($mutationAProcess1, 1),
                new SetClockAt(2_000_000_090),
                new MutantProcessExecutionWasFinished($mutationAProcess1),
                new SetClockAt(2_000_000_110),
                new MutantEvaluationWasFinished($mutantA),
                new SetClockAt(3_000_000_000),
                new MutantEvaluationWasStarted($mutantB),
                new SetClockAt(3_000_000_025),
                new MutantProcessExecutionWasStarted($mutationBProcess0, 2),
                new SetClockAt(3_000_000_055),
                new MutantProcessExecutionWasFinished($mutationBProcess0),
                new SetClockAt(3_000_000_115),
                new MutantProcessExecutionWasStarted($mutationBProcess1, 2),
                new SetClockAt(3_000_000_145),
                new MutantProcessExecutionWasFinished($mutationBProcess1),
                new SetClockAt(3_000_000_200),
                new MutantEvaluationWasFinished($mutantB),
            ],
        );

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
            if ($event instanceof SetClockAt) {
                $this->clock->setTime($event->time);

                continue;
            }

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
        $matchingSpans = array_values(
            array_filter(
                $this->exporter->getSpans(),
                static fn (SpanDataInterface $span): bool => $span->getName() === $name,
            ),
        );

        $this->assertCount(
            1,
            $matchingSpans,
            sprintf(
                'Expected exactly one span named "%s".',
                $name,
            ),
        );

        return $matchingSpans[0];
    }

    private function getMutantProcessExecutionSpanForThread(int $thread): SpanDataInterface
    {
        $matchingSpans = array_values(
            array_filter(
                $this->exporter->getSpans(),
                static fn (SpanDataInterface $span): bool => $span->getName() === 'infection.mutation_evaluation.mutant_analysis.evaluation.process'
                    && $span->getAttributes()->get('infection.mutation.process.thread') === $thread,
            ),
        );

        $this->assertCount(
            1,
            $matchingSpans,
            sprintf(
                'Expected exactly one mutant process execution span for thread "%d".',
                $thread,
            ),
        );

        return $matchingSpans[0];
    }

    private function getHeuristicSpanForHeuristic(HeuristicName $heuristic): SpanDataInterface
    {
        $matchingSpans = array_values(
            array_filter(
                $this->exporter->getSpans(),
                static fn (SpanDataInterface $span): bool => $span->getName() === 'infection.mutation_evaluation.mutation.heuristic'
                    && $span->getAttributes()->get('infection.mutation_evaluation.heuristic.id') === $heuristic->value,
            ),
        );

        $this->assertCount(
            1,
            $matchingSpans,
            sprintf(
                'Expected exactly one heuristic span for heuristic "%s".',
                $heuristic->value,
            ),
        );

        return $matchingSpans[0];
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
        $matchingSpans = array_values(
            array_filter(
                $this->exporter->getSpans(),
                static fn (SpanDataInterface $span): bool => $span->getName() === 'infection.mutation_evaluation.mutant_analysis.evaluation'
                    && $span->getAttributes()->get('infection.mutation.id') === $mutationHash,
            ),
        );

        $this->assertCount(
            1,
            $matchingSpans,
            sprintf(
                'Expected exactly one mutant evaluation span for mutation "%s".',
                $mutationHash,
            ),
        );

        return $matchingSpans[0];
    }

    /**
     * @return list<string>
     */
    private function getExportedMutationIds(): array
    {
        $mutationIds = [];

        foreach ($this->exporter->getSpans() as $span) {
            if ($span->getAttributes()->has('infection.mutation.id')) {
                $mutationIds[] = $span->getAttributes()->get('infection.mutation.id');
            }
        }

        return $mutationIds;
    }

    /**
     * @return list<string>
     */
    private function getExportedHeuristicIds(): array
    {
        $heuristicIds = [];

        foreach ($this->exporter->getSpans() as $span) {
            if ($span->getAttributes()->has('infection.mutation_evaluation.heuristic.id')) {
                $heuristicIds[] = $span->getAttributes()->get('infection.mutation_evaluation.heuristic.id');
            }
        }

        return $heuristicIds;
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

    /**
     * @param Attributes $expected
     */
    private function assertSpanAttributesEquals(array $expected, SpanDataInterface $span): void
    {
        $this->assertEqualsCanonicalizing($expected, $span->getAttributes()->toArray());
    }

    /**
     * @param class-string $startEventClass
     * @param class-string $endEventClass
     * @param Attributes $attributes
     *
     * @return Attributes
     */
    private static function createAttributes(
        string $startEventClass,
        string $endEventClass,
        array $attributes = [],
    ): array {
        return [
            'infection.event.class.start' => $startEventClass,
            'infection.event.class.end' => $endEventClass,
            ...$attributes,
        ];
    }

    private function assertTracerProviderWasShutdown(): void
    {
        $this->assertFalse($this->tracerProvider->getTracer('infection')->isEnabled());
    }
}
