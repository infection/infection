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

namespace Infection\Telemetry\Listener;

use Infection\Event\ApplicationExecutionWasFinished;
use Infection\Event\ApplicationExecutionWasStarted;
use Infection\Event\FileParsingWasFinished;
use Infection\Event\FileParsingWasStarted;
use Infection\Event\InitialStaticAnalysisRunWasFinished;
use Infection\Event\InitialStaticAnalysisRunWasStarted;
use Infection\Event\InitialTestSuiteWasFinished;
use Infection\Event\InitialTestSuiteWasStarted;
use Infection\Event\MutantAnalysisWasFinished;
use Infection\Event\MutantAnalysisWasStarted;
use Infection\Event\MutationAnalysisWasStarted;
use Infection\Event\MutationGenerationWasFinished;
use Infection\Event\MutationGenerationWasStarted;
use Infection\Event\MutationHeuristicsWasFinished;
use Infection\Event\MutationHeuristicsWasStarted;
use Infection\Event\MutationAnalysisWasFinished;
use Infection\Event\Subscriber\EventSubscriber;
use Infection\Mutation\Mutation;
use Infection\Resource\Memory\MemoryFormatter;
use Infection\Resource\Time\Stopwatch;
use Infection\Resource\Time\TimeFormatter;
use Infection\Telemetry\Tracing\RootScopes;
use Infection\Telemetry\Tracing\Span;
use Infection\Telemetry\Tracing\SpanBuilder;
use Infection\Telemetry\Tracing\SpanRecorder;
use Infection\Telemetry\Tracing\Tracer;
use Infection\Utility\UniqueId;
use SplObjectStorage;
use Symfony\Component\Console\Output\OutputInterface;
use function memory_get_peak_usage;
use function spl_object_id;
use function sprintf;

/**
 * @internal
 */
final class TracingSubscriber implements EventSubscriber
{
    private SpanBuilder $initialTestSuiteSpan;
    private SpanBuilder $initialStaticAnalysisRunSpan;

    // will only contain the spans about the mutation generation
    private SpanBuilder $mutationGenerationSpan;

    // will only contain the spans about the mutation analysis
    private SpanBuilder $mutationAnalysisSpan;

    /** @var array<int, SpanBuilder> The spans for an entire file (parsing + generation + execution) */
    private array $fileSpans = [];

    /** @var array<int, SpanBuilder> Only the parsing part; child of mutationGeneration */
    private array $fileParsingSpansForMutationGeneration = [];
    /** @var array<int, SpanBuilder> Only the parsing part; child of fileSpans */
    private array $fileParsingSpansForFileSpans = [];

    /** @var array<int, SpanBuilder> Span for the mutation analysis of a single mutation; child of fileSpans */
    private array $individualMutationAnalysisSpans = [];

    /** @var array<int, SpanBuilder> Child of mutationAnalysis */
    private array $mutationHeuristicsCheckSpans = [];

    /** @var array<int, SpanBuilder> Child of mutationAnalysis */
    private array $mutationExecutionSpans = [];

    public function __construct(
        private readonly Tracer $tracer,
    ) {
    }

    public function whenInitialTestSuiteWasStarted(InitialTestSuiteWasStarted $event): void
    {
        $this->initialTestSuiteSpan = $this->tracer->startSpan(
            RootScopes::INITIAL_TEST_SUITE,
            UniqueId::generate(),
        );
    }

    public function whenInitialTestSuiteWasFinished(InitialTestSuiteWasFinished $event): void
    {
        $this->tracer->finishSpan($this->initialTestSuiteSpan);
    }

    public function whenInitialStaticAnalysisRunWasStarted(InitialStaticAnalysisRunWasStarted $event): void
    {
        $this->initialStaticAnalysisRunSpan = $this->tracer->startSpan(
            RootScopes::INITIAL_STATIC_ANALYSIS,
            UniqueId::generate(),
        );
    }

    public function whenInitialStaticAnalysisRunWasFinished(InitialStaticAnalysisRunWasFinished $event): void
    {
        $this->tracer->finishSpan($this->initialStaticAnalysisRunSpan);
    }

    public function whenMutationGenerationWasStarted(MutationGenerationWasStarted $event): void
    {
        $this->mutationGenerationSpan = $this->tracer->startSpan(
            RootScopes::MUTATION_GENERATION,
            UniqueId::generate(),
        );
    }

    public function whenMutationGenerationWasFinished(MutationGenerationWasFinished $event): void
    {
        $this->tracer->finishSpan($this->mutationGenerationSpan);
    }

    // This is the general process; not for a specific mutation
    public function whenMutationTestingWasStarted(MutationAnalysisWasStarted $event): void
    {
        $this->mutationAnalysisSpan = $this->tracer->startSpan(
            RootScopes::MUTATION_ANALYSIS,
            UniqueId::generate(),
        );
    }

    public function whenMutationAnalysisWasFinished(MutationAnalysisWasFinished $event): void
    {
        $this->tracer->finishSpan($this->mutationAnalysisSpan);
    }

    public function whenFileParsingWasStarted(FileParsingWasStarted $event): void
    {
        $trace = $event->trace;
        $traceId = spl_object_id($trace);

        $fileSpan = $this->tracer->startSpan(
            RootScopes::FILE,
            $traceId,
        );
        $this->fileSpans[$traceId] = $fileSpan;

        $parsingSpan = $this->tracer->startChildSpan(
            'parsing',
            $traceId,
            $fileSpan,
        );

        $this->fileParsingSpansForMutationGeneration[$traceId] = $parsingSpan;
        $this->mutationGenerationSpan->addChild($parsingSpan);
    }

    public function whenFileParsingWasFinished(FileParsingWasFinished $event): void
    {
        $traceId = spl_object_id($event->trace);

        $this->tracer->finishSpan($this->fileParsingSpansForMutationGeneration[$traceId]);
    }

    public function whenMutationHeuristicsWasStarted(MutationHeuristicsWasStarted $event): void
    {
        $mutation = $event->mutation;
        $mutationId = $mutation->getHash();
        $traceId = spl_object_id($mutation->trace);

        $mutationAnalysisSpan = $this->tracer->startChildSpan(
            'mutation_analysis',
            $mutationId,
            $this->fileSpans[$traceId],
        );

        $this->fileSpans[$traceId]->addChild($mutationAnalysisSpan);

        $heuristicsSpan = $this->tracer->startChildSpan(
            'heuristics',
            $mutationId,
            $mutationAnalysisSpan,
        );

        $this->mutationAnalysisSpan->addChild($mutationAnalysisSpan);

        $this->individualMutationAnalysisSpans[$mutationId] = $mutationAnalysisSpan;
        $this->mutationHeuristicsCheckSpans[$mutationId] = $heuristicsSpan;
    }

    public function whenMutationHeuristicsWasFinished(MutationHeuristicsWasFinished $event): void
    {
        $mutationId = $event->mutation->getHash();

        $spansToFinish = [$this->mutationHeuristicsCheckSpans[$mutationId]];

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
