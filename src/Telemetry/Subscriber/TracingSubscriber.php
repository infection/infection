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
use Infection\Event\Events\Ast\AstGenerationWasFinished;
use Infection\Event\Events\Ast\AstGenerationWasFinishedSubscriber;
use Infection\Event\Events\Ast\AstGenerationWasStarted;
use Infection\Event\Events\Ast\AstGenerationWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasStartedSubscriber;
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
use Infection\Framework\UniqueId;
use Infection\Telemetry\Tracing\RootScope;
use Infection\Telemetry\Tracing\Scope;
use Infection\Telemetry\Tracing\SpanBuilder;
use Infection\Telemetry\Tracing\Tracer;

/**
 * @internal
 */
final class TracingSubscriber implements ArtefactCollectionWasFinishedSubscriber, ArtefactCollectionWasStartedSubscriber, AstGenerationWasFinishedSubscriber, AstGenerationWasStartedSubscriber, InitialStaticAnalysisRunWasFinishedSubscriber, InitialStaticAnalysisRunWasStartedSubscriber, InitialTestSuiteWasFinishedSubscriber, InitialTestSuiteWasStartedSubscriber, MutationAnalysisWasFinishedSubscriber, MutationAnalysisWasStartedSubscriber, MutationGenerationForFileWasFinishedSubscriber, MutationGenerationForFileWasStartedSubscriber, MutationGenerationWasFinishedSubscriber, MutationGenerationWasStartedSubscriber, MutationHeuristicsWasFinishedSubscriber, MutationHeuristicsWasStartedSubscriber, MutationTestingWasFinishedSubscriber, MutationTestingWasStartedSubscriber
{
    private SpanBuilder $artefactCollectionSpan;

    private SpanBuilder $initialTestSuiteSpan;

    private SpanBuilder $initialStaticAnalysisRunSpan;

    private SpanBuilder $mutationGenerationSpan;

    private SpanBuilder $mutationAnalysisSpan;

    private SpanBuilder $mutationEvaluationSpan;

    /** @var array<int, SpanBuilder> */
    private array $sourceFileSpans = [];

    /** @var array<int, SpanBuilder> */
    private array $astGenerationSpans = [];

    /** @var array<int, SpanBuilder> */
    private array $sourceFileMutationGenerationSpan = [];

    /** @var array<int, SpanBuilder> */
    private array $individualMutationAnalysisSpans = [];

    /** @var array<int, SpanBuilder> */
    private array $mutationHeuristicsSpans = [];

    /** @var array<int, SpanBuilder> */
    private array $mutationMutantSpans = [];

    /** @var array<int, SpanBuilder> */
    private array $mutationExecutionSpans = [];

    public function __construct(
        private readonly Tracer $tracer,
    ) {
    }

    public function onArtefactCollectionWasStarted(ArtefactCollectionWasStarted $event): void
    {
        $this->artefactCollectionSpan = $this->tracer->startSpan(
            RootScope::ARTEFACT_COLLECTION,
            UniqueId::generate(),
        );
    }

    public function onArtefactCollectionWasFinished(ArtefactCollectionWasFinished $event): void
    {
        $this->tracer->finishSpan($this->artefactCollectionSpan);
    }

    public function onInitialTestSuiteWasStarted(InitialTestSuiteWasStarted $event): void
    {
        $this->initialTestSuiteSpan = $this->tracer->startChildSpan(
            Scope::INITIAL_TESTS,
            UniqueId::generate(),
            $this->artefactCollectionSpan,
        );
    }

    public function onInitialTestSuiteWasFinished(InitialTestSuiteWasFinished $event): void
    {
        $this->tracer->finishSpan($this->initialTestSuiteSpan);
    }

    public function onInitialStaticAnalysisRunWasStarted(InitialStaticAnalysisRunWasStarted $event): void
    {
        $this->initialStaticAnalysisRunSpan = $this->tracer->startChildSpan(
            Scope::INITIAL_STATIC_ANALYSIS,
            UniqueId::generate(),
            $this->artefactCollectionSpan,
        );
    }

    public function onInitialStaticAnalysisRunWasFinished(InitialStaticAnalysisRunWasFinished $event): void
    {
        $this->tracer->finishSpan($this->initialStaticAnalysisRunSpan);
    }

    public function onMutationAnalysisWasStarted(MutationAnalysisWasStarted $event): void
    {
        $this->mutationAnalysisSpan = $this->tracer->startSpan(
            RootScope::MUTATION_ANALYSIS,
            UniqueId::generate(),
        );
    }

    public function onMutationAnalysisWasFinished(MutationAnalysisWasFinished $event): void
    {
        $this->tracer->finishSpan($this->mutationAnalysisSpan);
    }

    public function onMutationGenerationWasStarted(MutationGenerationWasStarted $event): void
    {
        $this->mutationGenerationSpan = $this->tracer->startChildSpan(
            Scope::MUTATION_GENERATION,
            UniqueId::generate(),
            $this->mutationAnalysisSpan,
        );
    }

    public function onMutationGenerationWasFinished(MutationGenerationWasFinished $event): void
    {
        $this->tracer->finishSpan($this->mutationGenerationSpan);
    }

    public function onAstGenerationWasStarted(AstGenerationWasStarted $event): void
    {
        $sourceFileId = $event->sourceFileId;

        $sourceFileSpan = $this->tracer->startSpan(
            RootScope::SOURCE_FILE,
            $sourceFileId,
        );
        $this->sourceFileSpans[$sourceFileId] = $sourceFileSpan;

        $astGenerationSpan = $this->tracer->startChildSpan(
            Scope::AST_GENERATION,
            $sourceFileId,
            $sourceFileSpan,
        );

        $this->astGenerationSpans[$sourceFileId] = $astGenerationSpan;
        $this->mutationGenerationSpan->addChild($astGenerationSpan);
    }

    public function onAstGenerationWasFinished(AstGenerationWasFinished $event): void
    {
        $this->tracer->finishSpan(
            $this->astGenerationSpans[$event->sourceFileId],
        );
    }

    public function onMutationGenerationForFileWasStarted(MutationGenerationForFileWasStarted $event): void
    {
        $sourceFileId = $event->sourceFileId;
        $sourceFileSpan = $this->sourceFileSpans[$sourceFileId];

        $sourceFileMutationGenerationSpan = $this->tracer->startChildSpan(
            Scope::AST_GENERATION,
            $sourceFileId,
            $sourceFileSpan,
        );

        $this->sourceFileMutationGenerationSpan[$sourceFileId] = $sourceFileMutationGenerationSpan;
        $this->mutationGenerationSpan->addChild($sourceFileMutationGenerationSpan);
    }

    public function onMutationGenerationForFileWasFinished(MutationGenerationForFileWasFinished $event): void
    {
        $this->tracer->finishSpan(
            $this->sourceFileMutationGenerationSpan[$event->sourceFileId],
        );
    }

    public function onMutationTestingWasStarted(MutationTestingWasStarted $event): void
    {
        $this->mutationEvaluationSpan = $this->tracer->startChildSpan(
            Scope::MUTATION_EVALUATION,
            UniqueId::generate(),
            $this->mutationAnalysisSpan,
        );
    }

    public function onMutationTestingWasFinished(MutationTestingWasFinished $event): void
    {
        $this->tracer->finishSpan($this->mutationEvaluationSpan);
    }

    public function onMutationHeuristicsWasStarted(MutationHeuristicsWasStarted $event): void
    {
        $sourceFileId = $event->sourceFileId;

        $mutation = $event->mutation;
        $mutationId = $mutation->getHash();

        $mutationAnalysisSpan = $this->tracer->startChildSpan(
            Scope::MUTATION_EVALUATION,
            $mutationId,
            $this->sourceFileSpans[$sourceFileId],
        );
        $this->mutationAnalysisSpan->addChild($mutationAnalysisSpan);
        $this->individualMutationAnalysisSpans[$mutationId] = $mutationAnalysisSpan;

        $heuristicsSpan = $this->tracer->startChildSpan(
            Scope::MUTATION_HEURISTICS,
            $mutationId,
            $mutationAnalysisSpan,
        );
        $this->mutationHeuristicsSpans[$mutationId] = $heuristicsSpan;
        $this->mutationAnalysisSpan->addChild($mutationAnalysisSpan);
    }

    public function onMutationHeuristicsWasFinished(MutationHeuristicsWasFinished $event): void
    {
        $mutationId = $event->mutation->getHash();

        $spansToFinish = [$this->mutationHeuristicsSpans[$mutationId]];

        if (!$event->escaped) {
            $spansToFinish[] = $this->individualMutationAnalysisSpans[$mutationId];
        }

        $this->tracer->finishSpan(...$spansToFinish);
    }

    public function whenMutantAnalysisWasStarted(MutantAnalysisWasStarted $event): void
    {
        $mutation = $event->mutant->getMutation();
        $mutationId = $mutation->getHash();

        $mutationExecutionSpan = $this->tracer->startChildSpan(
            'execution',
            $mutationId,
            $this->individualMutationAnalysisSpans[$mutationId],
        );

        $this->mutationExecutionSpans[$mutationId] = $mutationExecutionSpan;
    }

    public function whenMutantAnalysisWasFinished(MutantAnalysisWasFinished $event): void
    {
        $mutationId = $event->getExecutionResult()->getMutantHash();

        $this->tracer->finishSpan(
            $this->mutationExecutionSpans[$mutationId],
            $this->individualMutationAnalysisSpans[$mutationId],
        );
    }
}
