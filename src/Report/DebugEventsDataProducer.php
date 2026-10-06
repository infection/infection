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

namespace Infection\Report;

use Infection\Event\EventDispatcher\EventCollectingEventDispatcher;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantProcessWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutableFileWasProcessed;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasStarted;
use Infection\Report\Framework\DataProducer;
use function json_encode;
use const JSON_INVALID_UTF8_SUBSTITUTE;
use const JSON_THROW_ON_ERROR;
use Override;

/**
 * Produces a JSONL trace from the collected events.
 *
 * @internal
 */
final readonly class DebugEventsDataProducer implements DataProducer
{
    public function __construct(
        private EventCollectingEventDispatcher $dispatcher,
    ) {
    }

    /**
     * @return iterable<string>
     */
    #[Override]
    public function produce(): iterable
    {
        foreach ($this->dispatcher->getEvents() as $index => $event) {
            yield json_encode(
                [
                    'sequence' => $index + 1,
                    'event' => $event::class,
                    'data' => (object) self::getData($event),
                ],
                JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE,
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function getData(object $event): array
    {
        // Serialize selected values explicitly: ASTs, process runners and lazy code
        // must not be traversed or forced just to report an event. Custom events are
        // recorded by name; their payloads have no known serialization contract.
        return match (true) {
            $event instanceof InitialTestSuiteWasStarted => [
                'commandLine' => $event->commandLine,
            ],
            $event instanceof InitialTestSuiteWasFinished,
            $event instanceof InitialStaticAnalysisRunWasFinished => [
                'outputText' => $event->outputText,
            ],
            $event instanceof MutationGenerationWasStarted => [
                'mutableFilesCount' => $event->mutableFilesCount,
            ],
            $event instanceof MutableFileWasProcessed => [
                'sourceFilePath' => $event->sourceFilePath,
                'mutationHashes' => $event->mutationHashes,
            ],
            $event instanceof MutationTestingWasStarted => [
                'mutationCount' => $event->mutationCount,
                'processRunner' => $event->processRunner::class,
            ],
            $event instanceof MutationEvaluationWasStarted => [
                'mutation' => [
                    'hash' => $event->mutation->getHash(),
                    'mutatorClass' => $event->mutation->getMutatorClass(),
                    'mutatorName' => $event->mutation->getMutatorName(),
                    'originalFilePath' => $event->mutation->getOriginalFilePath(),
                    'attributes' => $event->mutation->getAttributes(),
                    'coveredByTests' => $event->mutation->isCoveredByTest(),
                ],
            ],
            $event instanceof MutantProcessWasFinished => [
                'executionResult' => [
                    'mutantHash' => $event->executionResult->getMutantHash(),
                    'mutatorClass' => $event->executionResult->getMutatorClass(),
                    'mutatorName' => $event->executionResult->getMutatorName(),
                    'originalFilePath' => $event->executionResult->getOriginalFilePath(),
                    'originalStartingLine' => $event->executionResult->getOriginalStartingLine(),
                    'originalEndingLine' => $event->executionResult->getOriginalEndingLine(),
                    'detectionStatus' => $event->executionResult->getDetectionStatus()->value,
                    'processCommandLine' => $event->executionResult->getProcessCommandLine(),
                    'processOutput' => $event->executionResult->getProcessOutput(),
                    'processRuntime' => $event->executionResult->getProcessRuntime(),
                ],
            ],
            default => [],
        };
    }
}
