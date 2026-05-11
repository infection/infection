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

namespace Infection\Event\Subscriber;

use function count;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantProcessWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantProcessWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationForMutationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationForMutationWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluationWasFinishedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationEvaluationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluationWasStartedSubscriber;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutableFileWasProcessed;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutableFileWasProcessedSubscriber;
use Infection\Logger\MutationAnalysis\MutationAnalysisLogger;

/**
 * @internal
 */
final readonly class MutationAnalysisLoggerSubscriber implements MutableFileWasProcessedSubscriber, MutantProcessWasFinishedSubscriber, MutationAnalysisWasFinishedSubscriber, MutationAnalysisWasStartedSubscriber, MutationEvaluationForMutationWasStartedSubscriber, MutationEvaluationWasFinishedSubscriber, MutationEvaluationWasStartedSubscriber
{
    public function __construct(
        private MutationAnalysisLogger $logger,
    ) {
    }

    public function onMutationAnalysisWasStarted(MutationAnalysisWasStarted $event): void
    {
        $this->logger->startAnalysis();
    }

    public function onMutationEvaluationWasStarted(MutationEvaluationWasStarted $event): void
    {
        $this->logger->startEvaluation($event->mutationCount);
    }

    public function onMutationEvaluationForMutationWasStarted(MutationEvaluationForMutationWasStarted $event): void
    {
        $this->logger->startEvaluationForMutation($event->mutation);
    }

    public function onMutableFileWasProcessed(MutableFileWasProcessed $event): void
    {
        if (count($event->mutationHashes) > 0) {
            $this->logger->finishMutationGenerationForFile(
                $event->sourceFilePath,
                $event->mutationHashes,
            );
        }
    }

    public function onMutantProcessWasFinished(MutantProcessWasFinished $event): void
    {
        $executionResult = $event->executionResult;

        $this->logger->finishEvaluationForMutation($executionResult);
    }

    public function onMutationEvaluationWasFinished(MutationEvaluationWasFinished $event): void
    {
        $this->logger->finishEvaluation();
    }

    public function onMutationAnalysisWasFinished(MutationAnalysisWasFinished $event): void
    {
        $this->logger->finishAnalysis();
    }
}
