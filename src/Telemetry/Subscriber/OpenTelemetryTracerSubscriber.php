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
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantProcessWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantProcessWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationWasStartedSubscriber;
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
use Infection\Telemetry\OpenTelemetryTracer;
use Infection\Telemetry\SpanHandle;

/**
 * @internal
 */
final class OpenTelemetryTracerSubscriber implements ApplicationExecutionWasFinishedSubscriber, ApplicationExecutionWasStartedSubscriber, ArtefactCollectionWasFinishedSubscriber, ArtefactCollectionWasStartedSubscriber, InitialStaticAnalysisRunWasFinishedSubscriber, InitialStaticAnalysisRunWasStartedSubscriber, InitialTestSuiteWasFinishedSubscriber, InitialTestSuiteWasStartedSubscriber, MutantProcessWasFinishedSubscriber, MutationEvaluationWasStartedSubscriber, MutationGenerationWasFinishedSubscriber, MutationGenerationWasStartedSubscriber, MutationTestingWasFinishedSubscriber, MutationTestingWasStartedSubscriber, ReportingWasFinishedSubscriber, ReportingWasStartedSubscriber, SourceCollectionWasFinishedSubscriber, SourceCollectionWasStartedSubscriber
{
    private ?SpanHandle $rootSpan = null;

    private ?SpanHandle $sourceCollectionSpan = null;

    private ?SpanHandle $artefactCollectionSpan = null;

    private ?SpanHandle $initialTestsSpan = null;

    private ?SpanHandle $initialStaticAnalysisSpan = null;

    private ?SpanHandle $mutationGenerationSpan = null;

    private ?SpanHandle $mutationTestingSpan = null;

    private ?SpanHandle $reportingSpan = null;

    /** @var array<string, SpanHandle> */
    private array $mutationEvaluationSpans = [];

    public function __construct(
        private readonly OpenTelemetryTracer $telemetry,
    ) {
    }

    public function onApplicationExecutionWasStarted(ApplicationExecutionWasStarted $event): void
    {
        $this->rootSpan = $this->telemetry->startRootSpan('infection.run');
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
        $this->mutationGenerationSpan = $this->startChild(
            'infection.mutation_generation',
            ['infection.source_file.count' => $event->mutableFilesCount],
        );
    }

    public function onMutationGenerationWasFinished(MutationGenerationWasFinished $event): void
    {
        $this->end($this->mutationGenerationSpan);
        $this->mutationGenerationSpan = null;
    }

    public function onMutationTestingWasStarted(MutationTestingWasStarted $event): void
    {
        $this->mutationTestingSpan = $this->startChild(
            'infection.mutation_testing',
            ['infection.mutation.count' => $event->mutationCount],
        );
    }

    public function onMutationEvaluationWasStarted(MutationEvaluationWasStarted $event): void
    {
        $mutation = $event->mutation;

        $span = $this->startChild(
            'infection.mutation_evaluation',
            [
                'infection.mutation.id' => $mutation->getHash(),
                'infection.mutator.name' => $mutation->getMutatorName(),
                'code.file.path' => $mutation->getOriginalFilePath(),
                'code.line.start' => $mutation->getOriginalStartingLine(),
                'code.line.end' => $mutation->getOriginalEndingLine(),
            ],
            $this->mutationTestingSpan,
        );

        if ($span !== null) {
            $this->mutationEvaluationSpans[$mutation->getHash()] = $span;
        }
    }

    public function onMutantProcessWasFinished(MutantProcessWasFinished $event): void
    {
        $result = $event->executionResult;
        $hash = $result->getMutantHash();
        $span = $this->mutationEvaluationSpans[$hash] ?? null;

        unset($this->mutationEvaluationSpans[$hash]);

        $this->end(
            $span,
            [
                'infection.mutation.status' => $result->getDetectionStatus()->value,
                'infection.mutation.runtime' => $result->getProcessRuntime(),
            ],
        );
    }

    public function onMutationTestingWasFinished(MutationTestingWasFinished $event): void
    {
        foreach ($this->mutationEvaluationSpans as $span) {
            $this->end($span);
        }

        $this->mutationEvaluationSpans = [];
        $this->end($this->mutationTestingSpan);
        $this->mutationTestingSpan = null;
    }

    public function onApplicationExecutionWasFinished(ApplicationExecutionWasFinished $event): void
    {
        $this->end($this->initialTestsSpan);
        $this->end($this->initialStaticAnalysisSpan);
        $this->end($this->artefactCollectionSpan);
        $this->end($this->sourceCollectionSpan);
        $this->end($this->mutationGenerationSpan);

        foreach ($this->mutationEvaluationSpans as $span) {
            $this->end($span);
        }

        $this->end($this->mutationTestingSpan);
        $this->end($this->reportingSpan);
        $this->end($this->rootSpan);
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
        $this->end($this->reportingSpan);
        $this->reportingSpan = null;
    }

    public function onReportingWasStarted(ReportingWasStarted $event): void
    {
        $this->reportingSpan = $this->startChild('infection.reporting');
    }

    public function onSourceCollectionWasFinished(SourceCollectionWasFinished $event): void
    {
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
     * @param array<non-empty-string, bool|int|float|string> $attributes
     */
    private function startChild(string $name, array $attributes = [], ?SpanHandle $parent = null): ?SpanHandle
    {
        $parent ??= $this->rootSpan;

        return $parent === null
            ? null
            : $this->telemetry->startChildSpan($parent, $name, $attributes);
    }

    /**
     * @param array<non-empty-string, bool|int|float|string> $attributes
     */
    private function end(?SpanHandle $span, array $attributes = []): void
    {
        if ($span !== null) {
            $this->telemetry->end($span, $attributes);
        }
    }
}
