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

use function array_diff;
use function array_fill_keys;
use function array_key_exists;
use function array_keys;
use function count;
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
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantEvaluation\MutantEvaluationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantEvaluation\MutantEvaluationWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantEvaluation\MutantEvaluationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantEvaluation\MutantEvaluationWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantMaterialisation\MutantMaterialisationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantMaterialisation\MutantMaterialisationWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantMaterialisation\MutantMaterialisationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantMaterialisation\MutantMaterialisationWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantProcessWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantProcessWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationForMutationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationForMutationWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationHeuristicsWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationHeuristicsWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationHeuristicsWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationHeuristicsWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationForFileWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationForFileWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationForFileWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationForFileWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasStartedSubscriber;
use Infection\Event\Events\Reporting\ReportingWasFinished;
use Infection\Event\Events\Reporting\ReportingWasFinishedSubscriber;
use Infection\Event\Events\Reporting\ReportingWasStarted;
use Infection\Event\Events\Reporting\ReportingWasStartedSubscriber;
use Infection\Event\Events\SourceCollection\SourceCollectionWasFinished;
use Infection\Event\Events\SourceCollection\SourceCollectionWasFinishedSubscriber;
use Infection\Event\Events\SourceCollection\SourceCollectionWasStarted;
use Infection\Event\Events\SourceCollection\SourceCollectionWasStartedSubscriber;
use Infection\Telemetry\Tracing\RootScope;
use Infection\Telemetry\Tracing\Scope;
use Infection\Telemetry\Tracing\SpanBuilder;
use Infection\Telemetry\Tracing\Tracer;
use function spl_object_id;

/**
 * @internal
 */
final class TelemetrySubscriber implements ArtefactCollectionWasFinishedSubscriber, ArtefactCollectionWasStartedSubscriber, AstEnrichmentWasFinishedSubscriber, AstEnrichmentWasStartedSubscriber, AstParsingWasFinishedSubscriber, AstParsingWasStartedSubscriber, AstProcessingWasFinishedSubscriber, AstProcessingWasStartedSubscriber, InitialStaticAnalysisRunWasFinishedSubscriber, InitialStaticAnalysisRunWasStartedSubscriber, InitialTestSuiteWasFinishedSubscriber, InitialTestSuiteWasStartedSubscriber, MutantEvaluationWasFinishedSubscriber, MutantEvaluationWasStartedSubscriber, MutantMaterialisationWasFinishedSubscriber, MutantMaterialisationWasStartedSubscriber, MutantProcessWasFinishedSubscriber, MutationAnalysisWasFinishedSubscriber, MutationAnalysisWasStartedSubscriber, MutationEvaluationForMutationWasStartedSubscriber, MutationGenerationForFileWasFinishedSubscriber, MutationGenerationForFileWasStartedSubscriber, MutationGenerationWasFinishedSubscriber, MutationGenerationWasStartedSubscriber, MutationHeuristicsWasFinishedSubscriber, MutationHeuristicsWasStartedSubscriber, MutationTestingWasFinishedSubscriber, MutationTestingWasStartedSubscriber, ReportingWasFinishedSubscriber, ReportingWasStartedSubscriber, SourceCollectionWasFinishedSubscriber, SourceCollectionWasStartedSubscriber
{
    private SpanBuilder $sourceCollectionSpan;

    private SpanBuilder $artefactCollectionSpan;

    private SpanBuilder $initialTestSuiteSpan;

    private SpanBuilder $initialStaticAnalysisRunSpan;

    private SpanBuilder $mutationGenerationSpan;

    private SpanBuilder $mutationAnalysisSpan;

    private SpanBuilder $mutationEvaluationSpan;

    private SpanBuilder $reportingSpan;

    /** @var array<string, SpanBuilder> key=sourceFileId */
    private array $sourceFileSpans = [];

    /** @var array<string, SpanBuilder> key=sourceFileId */
    private array $astProcessingSpans = [];

    /** @var array<string, SpanBuilder> key=sourceFileId */
    private array $astParsingSpans = [];

    /** @var array<string, SpanBuilder> key=sourceFileId */
    private array $astEnrichmentSpans = [];

    /** @var array<string, SpanBuilder> key=sourceFileId */
    private array $sourceFileMutationGenerationSpan = [];

    /** @var array<string, SpanBuilder> key=mutationId */
    private array $individualMutationEvaluationSpans = [];

    /** @var array<string, SpanBuilder> key=spanId */
    private array $individualMutantEvaluationSpans = [];

    /** @var array<string, array<string, SpanBuilder>> key1=mutationId, key2=heuristicIdName */
    private array $mutationHeuristicsSpans = [];

    /** @var array<string, SpanBuilder> key=mutationId */
    private array $mutationMaterialisationSpans = [];

    /**
     * @var array<string, array<string, true>>
     */
    private array $finishedMutationHashesBySourceFileId = [];

    /**
     * @var array<string, array<string, true>>
     */
    private array $remainingMutationHashesBySourceFileId = [];

    /**
     * @var array<string, string>
     */
    private array $sourceFileIdByMutationHash = [];

    public function __construct(
        private readonly Tracer $tracer,
    ) {
    }

    public function onSourceCollectionWasStarted(SourceCollectionWasStarted $event): void
    {
        $this->sourceCollectionSpan = $this->tracer->startSpan(RootScope::SOURCE_COLLECTION);
    }

    public function onSourceCollectionWasFinished(SourceCollectionWasFinished $event): void
    {
        $this->tracer->endSpan(
            $this->sourceCollectionSpan,
            attributes: ['sourcesCount' => $event->sourcesCount],
        );
    }

    public function onArtefactCollectionWasStarted(ArtefactCollectionWasStarted $event): void
    {
        $this->artefactCollectionSpan = $this->tracer->startSpan(RootScope::ARTEFACT_COLLECTION);
    }

    public function onArtefactCollectionWasFinished(ArtefactCollectionWasFinished $event): void
    {
        $this->tracer->endSpan($this->artefactCollectionSpan);
    }

    public function onInitialTestSuiteWasStarted(InitialTestSuiteWasStarted $event): void
    {
        $this->initialTestSuiteSpan = $this->tracer->startChildSpan(
            $this->artefactCollectionSpan,
            Scope::INITIAL_TESTS,
            attributes: [
                'testFrameworkName' => $event->testFrameworkName,
                'testFrameworkVersion' => $event->testFrameworkVersion,
            ],
        );
    }

    public function onInitialTestSuiteWasFinished(InitialTestSuiteWasFinished $event): void
    {
        $this->tracer->endSpan($this->initialTestSuiteSpan);
    }

    public function onInitialStaticAnalysisRunWasStarted(InitialStaticAnalysisRunWasStarted $event): void
    {
        $this->initialStaticAnalysisRunSpan = $this->tracer->startChildSpan(
            $this->artefactCollectionSpan,
            Scope::INITIAL_STATIC_ANALYSIS,
        );
    }

    public function onInitialStaticAnalysisRunWasFinished(InitialStaticAnalysisRunWasFinished $event): void
    {
        $this->tracer->endSpan($this->initialStaticAnalysisRunSpan);
    }

    public function onMutationAnalysisWasStarted(MutationAnalysisWasStarted $event): void
    {
        $this->mutationAnalysisSpan = $this->tracer->startSpan(RootScope::MUTATION_ANALYSIS);
    }

    public function onMutationAnalysisWasFinished(MutationAnalysisWasFinished $event): void
    {
        $this->tracer->endSpan($this->mutationAnalysisSpan);
    }

    public function onMutationGenerationWasStarted(MutationGenerationWasStarted $event): void
    {
        $this->mutationGenerationSpan = $this->tracer->startChildSpan(
            $this->mutationAnalysisSpan,
            Scope::MUTATION_GENERATION,
        );
    }

    public function onMutationGenerationWasFinished(MutationGenerationWasFinished $event): void
    {
        $this->tracer->endSpan($this->mutationGenerationSpan);
    }

    public function onAstProcessingWasStarted(AstProcessingWasStarted $event): void
    {
        $sourceFileId = $event->sourceFileId;

        $sourceFileSpan = $this->tracer->startSpan(
            RootScope::SOURCE_FILE,
            $sourceFileId,
        );
        $this->sourceFileSpans[$sourceFileId] = $sourceFileSpan;

        $sourceFileSpan->setAttribute('sourceFile', $event->sourceFilePath);

        $astProcessingSpan = $this->tracer->startChildSpan(
            $sourceFileSpan,
            Scope::AST_PROCESSING,
            $sourceFileId,
            attributes: ['sourceFile' => $event->sourceFilePath],
        );

        $this->astProcessingSpans[$sourceFileId] = $astProcessingSpan;
        $this->mutationGenerationSpan->addChild($astProcessingSpan);
    }

    public function onAstParsingWasStarted(AstParsingWasStarted $event): void
    {
        $sourceFileId = $event->sourceFileId;
        $astProcessingSpan = $this->astProcessingSpans[$sourceFileId];

        $this->astParsingSpans[$sourceFileId] = $this->tracer->startChildSpan(
            $astProcessingSpan,
            Scope::AST_PARSING,
            $sourceFileId,
            attributes: ['sourceFile' => $event->sourceFilePath],
        );
    }

    public function onAstParsingWasFinished(AstParsingWasFinished $event): void
    {
        $sourceFileId = $event->sourceFileId;

        $this->tracer->endSpan($this->astParsingSpans[$sourceFileId]);
        unset($this->astParsingSpans[$sourceFileId]);
    }

    public function onAstEnrichmentWasStarted(AstEnrichmentWasStarted $event): void
    {
        $sourceFileId = $event->sourceFileId;
        $astProcessingSpan = $this->astProcessingSpans[$sourceFileId];

        $this->astEnrichmentSpans[$sourceFileId] = $this->tracer->startChildSpan(
            $astProcessingSpan,
            Scope::AST_ENRICHMENT,
            $sourceFileId,
            attributes: ['sourceFile' => $event->sourceFilePath],
        );
    }

    public function onAstEnrichmentWasFinished(AstEnrichmentWasFinished $event): void
    {
        $sourceFileId = $event->sourceFileId;

        $this->tracer->endSpan($this->astEnrichmentSpans[$sourceFileId]);
        unset($this->astEnrichmentSpans[$sourceFileId]);
    }

    public function onAstProcessingWasFinished(AstProcessingWasFinished $event): void
    {
        $sourceFileId = $event->sourceFileId;

        $this->tracer->endSpan($this->astProcessingSpans[$sourceFileId]);
        unset($this->astProcessingSpans[$sourceFileId]);
    }

    public function onMutationGenerationForFileWasStarted(MutationGenerationForFileWasStarted $event): void
    {
        $sourceFileId = $event->sourceFileId;
        $sourceFileSpan = $this->sourceFileSpans[$sourceFileId];

        $sourceFileMutationGenerationSpan = $this->tracer->startChildSpan(
            $sourceFileSpan,
            Scope::MUTATION_GENERATION_FOR_FILE,
            attributes: ['sourceFile' => $event->sourceRealPath],
        );

        $this->sourceFileMutationGenerationSpan[$sourceFileId] = $sourceFileMutationGenerationSpan;
        $this->mutationGenerationSpan->addChild($sourceFileMutationGenerationSpan);
    }

    public function onMutationGenerationForFileWasFinished(MutationGenerationForFileWasFinished $event): void
    {
        $this->tracer->endSpan(
            $this->sourceFileMutationGenerationSpan[$event->sourceFileId],
            attributes: [
                'mutationIds' => $event->mutationHashes,
                'mutationCount' => count($event->mutationHashes),
            ],
        );

        $this->registerMutationsForSourceFile(
            $event->sourceFileId,
            $event->mutationHashes,
        );

        $this->endFileSpanIfAllMutationsAreEvaluated($event->sourceFileId);
    }

    public function onMutationTestingWasStarted(MutationTestingWasStarted $event): void
    {
        $this->mutationEvaluationSpan = $this->tracer->startChildSpan(
            $this->mutationAnalysisSpan,
            Scope::MUTATION_EVALUATION,
        );
    }

    public function onMutationTestingWasFinished(MutationTestingWasFinished $event): void
    {
        $this->tracer->endSpan($this->mutationEvaluationSpan);
    }

    public function onMutationEvaluationForMutationWasStarted(MutationEvaluationForMutationWasStarted $event): void
    {
        $sourceFileId = $event->sourceFileId;

        $mutation = $event->mutation;
        $mutationId = $mutation->getHash();

        $this->sourceFileIdByMutationHash[$mutationId] = $sourceFileId;

        $mutationEvaluationSpan = $this->tracer->startChildSpan(
            $this->sourceFileSpans[$sourceFileId],
            Scope::MUTATION_EVALUATION_FOR_MUTATION,
            $mutationId,
            attributes: [
                'mutationId' => $event->mutation->getHash(),
                'mutatorClass' => $event->mutation->getMutatorClass(),
                'mutatorName' => $event->mutation->getMutatorName(),
            ],
        );
        $this->mutationEvaluationSpan->addChild($mutationEvaluationSpan);
        $this->individualMutationEvaluationSpans[$mutationId] = $mutationEvaluationSpan;
    }

    public function onMutationHeuristicsWasStarted(MutationHeuristicsWasStarted $event): void
    {
        $mutation = $event->mutation;
        $mutationId = $mutation->getHash();
        $heuristicId = $event->heuristicId;

        $mutationEvaluationSpan = $this->individualMutationEvaluationSpans[$mutationId];

        $heuristicsSpan = $this->tracer->startChildSpan(
            $mutationEvaluationSpan,
            Scope::HEURISTIC_SUPPRESSION,
            $mutationId . $heuristicId->name,
            attributes: [
                'heuristicKey' => $heuristicId->name,
                'heuristic' => $heuristicId->value,
            ],
        );

        $this->mutationHeuristicsSpans[$mutationId][$heuristicId->name] = $heuristicsSpan;
    }

    public function onMutationHeuristicsWasFinished(MutationHeuristicsWasFinished $event): void
    {
        $mutationId = $event->mutation->getHash();
        $sourceFileId = $this->sourceFileIdByMutationHash[$mutationId];

        $spansToFinish = [$this->mutationHeuristicsSpans[$mutationId][$event->heuristicId->name]];

        if (!$event->escaped) {
            $spansToFinish[] = $this->individualMutationEvaluationSpans[$mutationId];
            $this->markMutationAsFinished($sourceFileId, $mutationId);
        }

        $this->tracer->endSpan($spansToFinish);

        $this->endFileSpanIfAllMutationsAreEvaluated($sourceFileId);
    }

    public function onMutantMaterialisationWasStarted(MutantMaterialisationWasStarted $event): void
    {
        $mutation = $event->mutant->getMutation();
        $mutationId = $mutation->getHash();

        $mutationEvaluationSpan = $this->individualMutationEvaluationSpans[$mutationId];

        $materialisationSpan = $this->tracer->startChildSpan(
            $mutationEvaluationSpan,
            Scope::MUTATION_MATERIALISATION,
            $mutationId,
        );

        $this->mutationMaterialisationSpans[$mutationId] = $materialisationSpan;
    }

    public function onMutantMaterialisationWasFinished(MutantMaterialisationWasFinished $event): void
    {
        $mutation = $event->mutant->getMutation();
        $mutationId = $mutation->getHash();

        $materialisationSpan = $this->mutationMaterialisationSpans[$mutationId];

        $this->tracer->endSpan($materialisationSpan);
        unset($this->mutationMaterialisationSpans[$mutationId]);
    }

    public function onMutantEvaluationWasStarted(MutantEvaluationWasStarted $event): void
    {
        $mutantProcess = $event->mutantProcessContainer->getCurrent();
        $mutation = $mutantProcess->getMutant()->getMutation();
        $mutationId = $mutation->getHash();
        $spanId = $mutationId . spl_object_id($mutantProcess);

        $mutationEvaluationSpan = $this->individualMutationEvaluationSpans[$mutationId];

        $mutantEvaluationSpan = $this->tracer->startChildSpan(
            $mutationEvaluationSpan,
            Scope::MUTANT_EVALUATION,
            $spanId,    // should have an ID for this process instead...
            attributes: [
                'testFrameworkName' => $mutantProcess->testFrameworkName,
                'commandLine' => $mutantProcess->getProcess()->getCommandLine(),
            ],
        );

        $this->individualMutantEvaluationSpans[$spanId] = $mutantEvaluationSpan;
    }

    // Currently, this event is only dispatched if the mutant evaluation process continues
    public function onMutantEvaluationWasFinished(MutantEvaluationWasFinished $event): void
    {
        $mutantProcess = $event->mutantProcessContainer->getCurrent();
        $spanId = $mutantProcess->getMutant()->getMutation()->getHash() . spl_object_id($mutantProcess);

        $mutantEvaluationSpan = $this->individualMutantEvaluationSpans[$spanId];

        $this->tracer->endSpan($mutantEvaluationSpan);
        unset($this->individualMutantEvaluationSpans[$spanId]);
    }

    public function onMutantProcessWasFinished(MutantProcessWasFinished $event): void
    {
        $mutationId = $event->executionResult->getMutantHash();
        $sourceFileId = $this->sourceFileIdByMutationHash[$mutationId];

        $this->tracer->endSpan(
            $this->individualMutationEvaluationSpans[$mutationId],
            attributes: [
                'diff' => $event->executionResult->getMutantDiff(),
                'result' => $event->executionResult->getDetectionStatus(),
            ],
        );

        $this->markMutationAsFinished($sourceFileId, $mutationId);

        $this->endFileSpanIfAllMutationsAreEvaluated($sourceFileId);
    }

    public function onReportingWasStarted(ReportingWasStarted $event): void
    {
        $this->reportingSpan = $this->tracer->startSpan(RootScope::REPORTING);
    }

    public function onReportingWasFinished(ReportingWasFinished $event): void
    {
        $this->tracer->endSpan($this->reportingSpan);
    }

    /**
     * @param list<string> $mutationHashes
     */
    private function registerMutationsForSourceFile(
        string $sourceFileId,
        array $mutationHashes,
    ): void {
        $finishedMutationHashes = array_keys($this->finishedMutationHashesBySourceFileId[$sourceFileId] ?? []);
        $remainingMutationHashes = array_diff($mutationHashes, $finishedMutationHashes);

        $this->remainingMutationHashesBySourceFileId[$sourceFileId] = array_fill_keys($remainingMutationHashes, true);
    }

    private function markMutationAsFinished(string $sourceFileId, string $mutationHash): void
    {
        $this->finishedMutationHashesBySourceFileId[$sourceFileId][$mutationHash] = true;

        if (array_key_exists($sourceFileId, $this->remainingMutationHashesBySourceFileId)) {
            unset($this->remainingMutationHashesBySourceFileId[$sourceFileId][$mutationHash]);
        }
    }

    private function endFileSpanIfAllMutationsAreEvaluated(string $sourceFileId): void
    {
        if (
            !array_key_exists($sourceFileId, $this->sourceFileSpans)
            || !array_key_exists($sourceFileId, $this->remainingMutationHashesBySourceFileId)
        ) {
            return;
        }

        if (count($this->remainingMutationHashesBySourceFileId[$sourceFileId]) === 0) {
            $this->tracer->endSpan($this->sourceFileSpans[$sourceFileId]);

            $mutationHashes = array_keys($this->finishedMutationHashesBySourceFileId[$sourceFileId] ?? []);

            foreach ($mutationHashes as $mutationHash) {
                unset($this->sourceFileIdByMutationHash[$mutationHash]);
            }

            unset(
                $this->sourceFileSpans[$sourceFileId],
                $this->remainingMutationHashesBySourceFileId[$sourceFileId],
                $this->finishedMutationHashesBySourceFileId[$sourceFileId],
            );
        }
    }
}
