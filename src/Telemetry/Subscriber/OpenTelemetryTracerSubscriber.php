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

namespace Infection\Telemetry\Subscriber;

use function array_keys;
use Infection\Event\Events\Application\ApplicationExecutionWasFinished;
use Infection\Event\Events\Application\ApplicationExecutionWasFinishedSubscriber;
use Infection\Event\Events\Application\ApplicationExecutionWasStarted;
use Infection\Event\Events\Application\ApplicationExecutionWasStartedSubscriber;
use Infection\Event\Events\ArtefactCollection\ArtefactCollectionWasFinished;
use Infection\Event\Events\ArtefactCollection\ArtefactCollectionWasFinishedSubscriber;
use Infection\Event\Events\ArtefactCollection\ArtefactCollectionWasStarted;
use Infection\Event\Events\ArtefactCollection\ArtefactCollectionWasStartedSubscriber;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasFinishedSubscriber;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasStartedSubscriber;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinishedSubscriber;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStartedSubscriber;
use Infection\Event\Events\Ast\AstEnrichment\AstEnrichmentWasFinished;
use Infection\Event\Events\Ast\AstEnrichment\AstEnrichmentWasFinishedSubscriber;
use Infection\Event\Events\Ast\AstEnrichment\AstEnrichmentWasStarted;
use Infection\Event\Events\Ast\AstEnrichment\AstEnrichmentWasStartedSubscriber;
use Infection\Event\Events\Ast\AstParsing\AstParsingWasFinished;
use Infection\Event\Events\Ast\AstParsing\AstParsingWasFinishedSubscriber;
use Infection\Event\Events\Ast\AstParsing\AstParsingWasStarted;
use Infection\Event\Events\Ast\AstParsing\AstParsingWasStartedSubscriber;
use Infection\Event\Events\Ast\AstProcessingWasFinished;
use Infection\Event\Events\Ast\AstProcessingWasFinishedSubscriber;
use Infection\Event\Events\Ast\AstProcessingWasStarted;
use Infection\Event\Events\Ast\AstProcessingWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicSuppressionWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicSuppressionWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicSuppressionWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicSuppressionWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\HeuristicSuppression\HeuristicWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantAnalysisWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantAnalysisWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantAnalysisWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantAnalysisWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantEvaluation\MutantEvaluationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantEvaluation\MutantEvaluationWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantEvaluation\MutantEvaluationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantEvaluation\MutantEvaluationWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantEvaluation\MutantProcessExecutionWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantEvaluation\MutantProcessExecutionWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantEvaluation\MutantProcessExecutionWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantEvaluation\MutantProcessExecutionWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantMaterialisation\MutantMaterialisationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantMaterialisation\MutantMaterialisationWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantMaterialisation\MutantMaterialisationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantAnalysis\MutantMaterialisation\MutantMaterialisationWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationForMutationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationForMutationWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationForMutationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationForMutationWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluationWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluationWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasStartedSubscriber;
use Infection\Event\Events\Reporting\ReportingWasFinished;
use Infection\Event\Events\Reporting\ReportingWasFinishedSubscriber;
use Infection\Event\Events\Reporting\ReportingWasStarted;
use Infection\Event\Events\Reporting\ReportingWasStartedSubscriber;
use Infection\Event\Events\Reporting\ReporterWasFinished;
use Infection\Event\Events\Reporting\ReporterWasFinishedSubscriber;
use Infection\Event\Events\Reporting\ReporterWasStarted;
use Infection\Event\Events\Reporting\ReporterWasStartedSubscriber;
use Infection\Event\Events\SourceCollection\SourceCollectionWasFinished;
use Infection\Event\Events\SourceCollection\SourceCollectionWasFinishedSubscriber;
use Infection\Event\Events\SourceCollection\SourceCollectionWasStarted;
use Infection\Event\Events\SourceCollection\SourceCollectionWasStartedSubscriber;
use Infection\Telemetry\Attribute\MutationSpanAttributesProvider;
use Infection\Telemetry\Attribute\RunSpanAttributesProvider;
use Infection\Telemetry\OpenTelemetryTracer;
use Infection\Telemetry\ProjectRelativePathResolver;
use Infection\Telemetry\SpanHandle;
use function spl_object_id;
use function str_starts_with;

/**
 * @phpstan-import-type Attributes from RunSpanAttributesProvider
 *
 * @internal
 */
final class OpenTelemetryTracerSubscriber implements ApplicationExecutionWasFinishedSubscriber, ApplicationExecutionWasStartedSubscriber, ArtefactCollectionWasFinishedSubscriber, ArtefactCollectionWasStartedSubscriber, AstEnrichmentWasFinishedSubscriber, AstEnrichmentWasStartedSubscriber, AstParsingWasFinishedSubscriber, AstParsingWasStartedSubscriber, AstProcessingWasFinishedSubscriber, AstProcessingWasStartedSubscriber, HeuristicSuppressionWasFinishedSubscriber, HeuristicSuppressionWasStartedSubscriber, HeuristicWasFinishedSubscriber, HeuristicWasStartedSubscriber, InitialStaticAnalysisRunWasFinishedSubscriber, InitialStaticAnalysisRunWasStartedSubscriber, InitialTestSuiteWasFinishedSubscriber, InitialTestSuiteWasStartedSubscriber, MutantAnalysisWasFinishedSubscriber, MutantAnalysisWasStartedSubscriber, MutantEvaluationWasFinishedSubscriber, MutantEvaluationWasStartedSubscriber, MutantMaterialisationWasFinishedSubscriber, MutantMaterialisationWasStartedSubscriber, MutantProcessExecutionWasFinishedSubscriber, MutantProcessExecutionWasStartedSubscriber, MutationAnalysisWasFinishedSubscriber, MutationAnalysisWasStartedSubscriber, MutationEvaluationForMutationWasFinishedSubscriber, MutationEvaluationForMutationWasStartedSubscriber, MutationEvaluationWasFinishedSubscriber, MutationEvaluationWasStartedSubscriber, MutationGenerationWasFinishedSubscriber, MutationGenerationWasStartedSubscriber, ReporterWasFinishedSubscriber, ReporterWasStartedSubscriber, ReportingWasFinishedSubscriber, ReportingWasStartedSubscriber, SourceCollectionWasFinishedSubscriber, SourceCollectionWasStartedSubscriber
{
    private ?SpanHandle $rootSpan = null;

    private ?SpanHandle $sourceCollectionSpan = null;

    private ?SpanHandle $artefactCollectionSpan = null;

    private ?SpanHandle $initialTestsSpan = null;

    private ?SpanHandle $initialStaticAnalysisSpan = null;

    private ?SpanHandle $mutationAnalysisSpan = null;

    private ?SpanHandle $mutationGenerationSpan = null;

    private ?SpanHandle $mutationEvaluationSpan = null;

    private ?SpanHandle $reportingSpan = null;

    private ?SpanHandle $astProcessingSpan = null;

    /** @var array<string, SpanHandle> */
    private array $astProcessingFileSpans = [];

    /** @var array<string, SpanHandle> */
    private array $astParsingSpans = [];

    /** @var array<string, SpanHandle> */
    private array $astEnrichmentSpans = [];

    /** @var array<string, SpanHandle> */
    private array $mutationEvaluationSpans = [];

    /** @var array<string, SpanHandle> */
    private array $heuristicSuppressionSpans = [];

    /** @var array<string, SpanHandle> */
    private array $heuristicSpans = [];

    /** @var array<string, SpanHandle> */
    private array $mutantAnalysisSpans = [];

    /** @var array<string, SpanHandle> */
    private array $mutantMaterialisationSpans = [];

    /** @var array<string, SpanHandle> */
    private array $mutantEvaluationSpans = [];

    /** @var array<int, SpanHandle> */
    private array $mutantProcessExecutionSpans = [];

    /** @var array<int, string> */
    private array $mutantProcessExecutionSpanMutationHashes = [];

    /** @var array<int, SpanHandle> */
    private array $reporterSpans = [];

    private int $sourceFileCount = 0;

    /**
     * @var positive-int|0
     */
    private int $mutatedFileCount = 0;

    /**
     * @var positive-int|0
     */
    private int $mutationCount = 0;

    private int $evaluatedMutationCount = 0;

    public function __construct(
        private readonly OpenTelemetryTracer $telemetry,
        private readonly RunSpanAttributesProvider $runSpanAttributesProvider,
        private readonly MutationSpanAttributesProvider $mutationSpanAttributesProvider,
        private readonly ProjectRelativePathResolver $projectRelativePathResolver,
    ) {
    }

    public function onApplicationExecutionWasStarted(ApplicationExecutionWasStarted $event): void
    {
        $this->rootSpan = $this->telemetry->startRootSpan(
            'infection.run',
            $this->runSpanAttributesProvider->provideInitialAttributes(),
        );
    }

    public function onInitialTestSuiteWasStarted(InitialTestSuiteWasStarted $event): void
    {
        $this->initialTestsSpan = $this->startChild(
            'infection.initial_tests',
            parent: $this->artefactCollectionSpan,
        );
    }

    public function onInitialTestSuiteWasFinished(InitialTestSuiteWasFinished $event): void
    {
        $this->end($this->initialTestsSpan);
        $this->initialTestsSpan = null;
    }

    public function onInitialStaticAnalysisRunWasStarted(InitialStaticAnalysisRunWasStarted $event): void
    {
        $this->initialStaticAnalysisSpan = $this->startChild(
            'infection.initial_static_analysis',
            parent: $this->artefactCollectionSpan,
        );
    }

    public function onInitialStaticAnalysisRunWasFinished(InitialStaticAnalysisRunWasFinished $event): void
    {
        $this->end($this->initialStaticAnalysisSpan);
        $this->initialStaticAnalysisSpan = null;
    }

    public function onMutationGenerationWasStarted(MutationGenerationWasStarted $event): void
    {
        $this->endAstSpans();

        $this->mutationGenerationSpan = $this->startChild(
            'infection.mutation_generation',
            ['infection.source_file.count' => $event->mutableFilesCount],
            parent: $this->mutationAnalysisSpan,
        );
    }

    public function onMutationGenerationWasFinished(MutationGenerationWasFinished $event): void
    {
        $this->mutationCount = $event->mutationsCount;
        $this->mutatedFileCount = $event->mutatedFilesCount;

        $this->end(
            $this->mutationGenerationSpan,
            [
                'infection.mutated_file.count' => $event->mutatedFilesCount,
                'infection.mutation.generated.count' => $event->mutationsCount,
            ],
        );
        $this->mutationGenerationSpan = null;
    }

    public function onMutationAnalysisWasStarted(MutationAnalysisWasStarted $event): void
    {
        $this->mutationAnalysisSpan = $this->startChild('infection.mutation_analysis');
    }

    public function onMutationAnalysisWasFinished(MutationAnalysisWasFinished $event): void
    {
        $this->endAstSpans();
        $this->end($this->mutationGenerationSpan);
        $this->mutationGenerationSpan = null;

        foreach ($this->mutationEvaluationSpans as $span) {
            $this->end($span);
        }

        $this->mutationEvaluationSpans = [];
        $this->end($this->mutationEvaluationSpan);
        $this->mutationEvaluationSpan = null;
        $this->end($this->mutationAnalysisSpan);
        $this->mutationAnalysisSpan = null;
    }

    public function onAstProcessingWasStarted(AstProcessingWasStarted $event): void
    {
        $this->astProcessingSpan ??= $this->startChild(
            'infection.ast_processing',
            parent: $this->mutationAnalysisSpan,
        );

        $span = $this->startChild(
            'infection.ast_processing.file',
            ['code.file.path' => $this->projectRelativePathResolver->resolve($event->sourceFilePath)],
            parent: $this->astProcessingSpan,
        );

        if ($span !== null) {
            $this->astProcessingFileSpans[$event->sourceFilePath] = $span;
        }
    }

    public function onAstProcessingWasFinished(AstProcessingWasFinished $event): void
    {
        $span = $this->astProcessingFileSpans[$event->sourceFilePath] ?? null;

        unset($this->astProcessingFileSpans[$event->sourceFilePath]);

        $this->end($span);
    }

    public function onAstParsingWasStarted(AstParsingWasStarted $event): void
    {
        $span = $this->startChild(
            'infection.ast_processing.file.parsing',
            ['code.file.path' => $this->projectRelativePathResolver->resolve($event->sourceFilePath)],
            parent: $this->astProcessingFileSpans[$event->sourceFilePath] ?? null,
        );

        if ($span !== null) {
            $this->astParsingSpans[$event->sourceFilePath] = $span;
        }
    }

    public function onAstParsingWasFinished(AstParsingWasFinished $event): void
    {
        $span = $this->astParsingSpans[$event->sourceFilePath] ?? null;

        unset($this->astParsingSpans[$event->sourceFilePath]);

        $this->end($span);
    }

    public function onAstEnrichmentWasStarted(AstEnrichmentWasStarted $event): void
    {
        $span = $this->startChild(
            'infection.ast_processing.file.enrichment',
            ['code.file.path' => $this->projectRelativePathResolver->resolve($event->sourceFilePath)],
            parent: $this->astProcessingFileSpans[$event->sourceFilePath] ?? null,
        );

        if ($span !== null) {
            $this->astEnrichmentSpans[$event->sourceFilePath] = $span;
        }
    }

    public function onAstEnrichmentWasFinished(AstEnrichmentWasFinished $event): void
    {
        $span = $this->astEnrichmentSpans[$event->sourceFilePath] ?? null;

        unset($this->astEnrichmentSpans[$event->sourceFilePath]);

        $this->end($span);
    }

    public function onMutationEvaluationWasStarted(MutationEvaluationWasStarted $event): void
    {
        $this->mutationEvaluationSpan = $this->startChild(
            'infection.mutation_evaluation',
            parent: $this->mutationAnalysisSpan,
        );
    }

    public function onMutationEvaluationForMutationWasStarted(MutationEvaluationForMutationWasStarted $event): void
    {
        $mutation = $event->mutation;

        $span = $this->startChild(
            'infection.mutation_evaluation.mutation',
            $this->mutationSpanAttributesProvider->provide($mutation),
            $this->mutationEvaluationSpan,
        );

        if ($span !== null) {
            $this->mutationEvaluationSpans[$mutation->getHash()] = $span;
        }
    }

    public function onMutationEvaluationForMutationWasFinished(MutationEvaluationForMutationWasFinished $event): void
    {
        $result = $event->executionResult;
        $hash = $result->getMutantHash();
        $span = $this->mutationEvaluationSpans[$hash] ?? null;

        $this->endMutationEvaluationChildSpans($hash);

        unset($this->mutationEvaluationSpans[$hash]);

        $this->end(
            $span,
            [
                'infection.mutation.status' => $result->getDetectionStatus()->value,
                'infection.mutation.runtime' => $result->getProcessRuntime(),
            ],
        );
    }

    public function onHeuristicSuppressionWasStarted(HeuristicSuppressionWasStarted $event): void
    {
        $hash = $event->mutation->getHash();

        $span = $this->startChild(
            'infection.mutation_evaluation.mutation.heuristic_suppression',
            $this->mutationSpanAttributesProvider->provide($event->mutation),
            parent: $this->mutationEvaluationSpans[$hash] ?? null,
        );

        if ($span !== null) {
            $this->heuristicSuppressionSpans[$hash] = $span;
        }
    }

    public function onHeuristicSuppressionWasFinished(HeuristicSuppressionWasFinished $event): void
    {
        $hash = $event->mutation->getHash();

        $this->endHeuristicSpans($hash);
        $this->end($this->heuristicSuppressionSpans[$hash] ?? null);

        unset($this->heuristicSuppressionSpans[$hash]);
    }

    public function onHeuristicWasStarted(HeuristicWasStarted $event): void
    {
        $hash = $event->mutation->getHash();
        $key = self::heuristicKey($hash, $event->heuristic->value);

        $span = $this->startChild(
            'infection.mutation_evaluation.mutation.heuristic',
            [
                ...$this->mutationSpanAttributesProvider->provide($event->mutation),
                'infection.mutation_evaluation.heuristic.id' => $event->heuristic->value,
            ],
            $this->heuristicSuppressionSpans[$hash] ?? ($this->mutationEvaluationSpans[$hash] ?? null),
        );

        if ($span !== null) {
            $this->heuristicSpans[$key] = $span;
        }
    }

    public function onHeuristicWasFinished(HeuristicWasFinished $event): void
    {
        $key = self::heuristicKey($event->mutation->getHash(), $event->heuristic->value);

        $this->end($this->heuristicSpans[$key] ?? null);

        unset($this->heuristicSpans[$key]);
    }

    public function onMutantAnalysisWasStarted(MutantAnalysisWasStarted $event): void
    {
        $mutation = $event->mutant->getMutation();
        $hash = $mutation->getHash();

        $span = $this->startChild(
            'infection.mutation_evaluation.mutant_analysis',
            $this->mutationSpanAttributesProvider->provide($mutation),
            parent: $this->mutationEvaluationSpans[$hash] ?? null,
        );

        if ($span !== null) {
            $this->mutantAnalysisSpans[$hash] = $span;
        }
    }

    public function onMutantAnalysisWasFinished(MutantAnalysisWasFinished $event): void
    {
        $hash = $event->mutant->getMutation()->getHash();

        $this->end($this->mutantMaterialisationSpans[$hash] ?? null);
        unset($this->mutantMaterialisationSpans[$hash]);

        $this->end($this->mutantAnalysisSpans[$hash] ?? null);
        unset($this->mutantAnalysisSpans[$hash]);
    }

    public function onMutantMaterialisationWasStarted(MutantMaterialisationWasStarted $event): void
    {
        $mutation = $event->mutant->getMutation();
        $hash = $mutation->getHash();

        $span = $this->startChild(
            'infection.mutation_evaluation.mutant_analysis.materialisation',
            $this->mutationSpanAttributesProvider->provide($mutation),
            parent: $this->mutantAnalysisSpans[$hash] ?? ($this->mutationEvaluationSpans[$hash] ?? null),
        );

        if ($span !== null) {
            $this->mutantMaterialisationSpans[$hash] = $span;
        }
    }

    public function onMutantMaterialisationWasFinished(MutantMaterialisationWasFinished $event): void
    {
        $hash = $event->mutant->getMutation()->getHash();

        $this->end($this->mutantMaterialisationSpans[$hash] ?? null);

        unset($this->mutantMaterialisationSpans[$hash]);
    }

    public function onMutantEvaluationWasStarted(MutantEvaluationWasStarted $event): void
    {
        $mutation = $event->mutant->getMutation();
        $hash = $mutation->getHash();

        ++$this->evaluatedMutationCount;

        $span = $this->startChild(
            'infection.mutation_evaluation.mutant_analysis.evaluation',
            $this->mutationSpanAttributesProvider->provide($mutation),
            parent: $this->mutantAnalysisSpans[$hash] ?? ($this->mutationEvaluationSpans[$hash] ?? null),
        );

        if ($span !== null) {
            $this->mutantEvaluationSpans[$hash] = $span;
        }
    }

    public function onMutantEvaluationWasFinished(MutantEvaluationWasFinished $event): void
    {
        $hash = $event->mutant->getMutation()->getHash();

        $this->endMutantProcessExecutionSpans($hash);
        $this->end($this->mutantEvaluationSpans[$hash] ?? null);

        unset($this->mutantEvaluationSpans[$hash]);
    }

    public function onMutantProcessExecutionWasStarted(MutantProcessExecutionWasStarted $event): void
    {
        $mutation = $event->mutantProcess->getMutant()->getMutation();
        $hash = $mutation->getHash();
        $key = spl_object_id($event->mutantProcess);

        $span = $this->startChild(
            'infection.mutation_evaluation.mutant_analysis.evaluation.process',
            $this->mutationSpanAttributesProvider->provide($mutation),
            parent: $this->mutantEvaluationSpans[$hash] ?? ($this->mutationEvaluationSpans[$hash] ?? null),
        );

        if ($span !== null) {
            $this->mutantProcessExecutionSpans[$key] = $span;
            $this->mutantProcessExecutionSpanMutationHashes[$key] = $hash;
        }
    }

    public function onMutantProcessExecutionWasFinished(MutantProcessExecutionWasFinished $event): void
    {
        $key = spl_object_id($event->mutantProcess);

        $this->end($this->mutantProcessExecutionSpans[$key] ?? null);

        unset($this->mutantProcessExecutionSpans[$key]);
        unset($this->mutantProcessExecutionSpanMutationHashes[$key]);
    }

    public function onMutationEvaluationWasFinished(MutationEvaluationWasFinished $event): void
    {
        foreach (array_keys($this->mutationEvaluationSpans) as $hash) {
            $this->endMutationEvaluationChildSpans($hash);
            $this->end($this->mutationEvaluationSpans[$hash]);
        }

        $this->mutationEvaluationSpans = [];
        $this->end($this->mutationEvaluationSpan);
        $this->mutationEvaluationSpan = null;
    }

    public function onApplicationExecutionWasFinished(ApplicationExecutionWasFinished $event): void
    {
        $this->end($this->initialTestsSpan);
        $this->end($this->initialStaticAnalysisSpan);
        $this->end($this->artefactCollectionSpan);
        $this->end($this->sourceCollectionSpan);
        $this->endAstSpans();
        $this->end($this->mutationGenerationSpan);

        foreach (array_keys($this->mutationEvaluationSpans) as $hash) {
            $this->endMutationEvaluationChildSpans($hash);
            $this->end($this->mutationEvaluationSpans[$hash]);
        }

        $this->end($this->mutationEvaluationSpan);
        $this->end($this->mutationAnalysisSpan);
        $this->endReporterSpans();
        $this->end($this->reportingSpan);
        $this->end(
            $this->rootSpan,
            $this->runSpanAttributesProvider->provideSummaryAttributes(
                $this->sourceFileCount,
                $this->mutatedFileCount,
                $this->mutationCount,
                $this->evaluatedMutationCount,
            ),
        );
        $this->telemetry->shutdown();
    }

    public function onArtefactCollectionWasFinished(ArtefactCollectionWasFinished $event): void
    {
        $this->end($this->artefactCollectionSpan);
        $this->artefactCollectionSpan = null;
    }

    public function onArtefactCollectionWasStarted(ArtefactCollectionWasStarted $event): void
    {
        $this->artefactCollectionSpan = $this->startChild('infection.artefact_collection');
    }

    public function onReportingWasFinished(ReportingWasFinished $event): void
    {
        $this->endReporterSpans();
        $this->end($this->reportingSpan);
        $this->reportingSpan = null;
    }

    public function onReportingWasStarted(ReportingWasStarted $event): void
    {
        $this->reportingSpan = $this->startChild('infection.reporting');
    }

    public function onReporterWasStarted(ReporterWasStarted $event): void
    {
        if ($this->reportingSpan === null) {
            return;
        }

        $this->reporterSpans[$event->reporterId] = $this->telemetry->startChildSpan(
            $this->reportingSpan,
            'infection.reporting.reporter',
            [
                'infection.reporter.id' => $event->reporterId,
                'infection.reporter.name' => $event->name->value,
            ],
        );
    }

    public function onReporterWasFinished(ReporterWasFinished $event): void
    {
        $key = $event->reporterId;

        if (!isset($this->reporterSpans[$key])) {
            return;
        }

        $this->end($this->reporterSpans[$key]);
        unset($this->reporterSpans[$key]);
    }

    public function onSourceCollectionWasFinished(SourceCollectionWasFinished $event): void
    {
        $this->sourceFileCount = $event->sourcesCount;

        $this->end(
            $this->sourceCollectionSpan,
            ['infection.source_file.count' => $event->sourcesCount],
        );
        $this->sourceCollectionSpan = null;
    }

    public function onSourceCollectionWasStarted(SourceCollectionWasStarted $event): void
    {
        $this->sourceCollectionSpan = $this->startChild('infection.source_collection');
    }

    /**
     * @param non-empty-string $name
     * @param Attributes $attributes
     */
    private function startChild(string $name, array $attributes = [], ?SpanHandle $parent = null): ?SpanHandle
    {
        $parent ??= $this->rootSpan;

        return $parent === null
            ? null
            : $this->telemetry->startChildSpan($parent, $name, $attributes);
    }

    /**
     * @param Attributes $attributes
     */
    private function end(?SpanHandle $span, array $attributes = []): void
    {
        if ($span !== null) {
            $this->telemetry->end($span, $attributes);
        }
    }

    private function endReporterSpans(): void
    {
        foreach ($this->reporterSpans as $span) {
            $this->end($span);
        }

        $this->reporterSpans = [];
    }

    private function endAstSpans(): void
    {
        foreach ($this->astParsingSpans as $span) {
            $this->end($span);
        }

        foreach ($this->astEnrichmentSpans as $span) {
            $this->end($span);
        }

        foreach ($this->astProcessingFileSpans as $span) {
            $this->end($span);
        }

        $this->end($this->astProcessingSpan);

        $this->astParsingSpans = [];
        $this->astEnrichmentSpans = [];
        $this->astProcessingFileSpans = [];
        $this->astProcessingSpan = null;
    }

    private function endMutationEvaluationChildSpans(string $hash): void
    {
        $this->endHeuristicSpans($hash);
        $this->end($this->heuristicSuppressionSpans[$hash] ?? null);
        $this->end($this->mutantMaterialisationSpans[$hash] ?? null);
        $this->endMutantProcessExecutionSpans($hash);
        $this->end($this->mutantEvaluationSpans[$hash] ?? null);
        $this->end($this->mutantAnalysisSpans[$hash] ?? null);

        unset(
            $this->heuristicSuppressionSpans[$hash],
            $this->mutantMaterialisationSpans[$hash],
            $this->mutantEvaluationSpans[$hash],
            $this->mutantAnalysisSpans[$hash],
        );
    }

    private function endHeuristicSpans(string $hash): void
    {
        $prefix = $hash . ':';

        foreach ($this->heuristicSpans as $key => $span) {
            if (!str_starts_with($key, $prefix)) {
                continue;
            }

            $this->end($span);

            unset($this->heuristicSpans[$key]);
        }
    }

    private function endMutantProcessExecutionSpans(string $hash): void
    {
        foreach ($this->mutantProcessExecutionSpans as $key => $span) {
            if (($this->mutantProcessExecutionSpanMutationHashes[$key] ?? null) !== $hash) {
                continue;
            }

            $this->end($span);

            unset($this->mutantProcessExecutionSpans[$key]);
            unset($this->mutantProcessExecutionSpanMutationHashes[$key]);
        }
    }

    private static function heuristicKey(string $mutationHash, string $heuristic): string
    {
        return $mutationHash . ':' . $heuristic;
    }
}
