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

namespace Infection\Logger\MutationAnalysis\TeamCity;

use function array_fill_keys;
use function array_key_exists;
use function array_merge;
use function count;
use function hash;
use Infection\Logger\MutationAnalysis\MutationAnalysisLogger;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutation\Mutation;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;

// TODO: explain somewhere the concept of TestSuite and Test for TeamCity
final class TeamCityLogger implements MutationAnalysisLogger
{
    /**
     * @var array<string, array<string, true>>
     */
    private array $evaluatedMutationIdsBySourceFilePath = [];

    /**
     * Populated when all the mutations were generated. This may happen while
     * some mutants are still being evaluated.
     *
     * @var array<string, list<string>>
     */
    private array $generatedMutationIdsBySourceFilePath = [];

    /**
     * @var array<string, array{'name': string, 'flowId': string}> The index is the unchanged (absolute) source path of the source file.
     */
    private array $testSuites = [];

    /**
     * Maps mutation hash to source file path.
     * This is needed because MutantExecutionResult may not have the correct source file path.
     *
     * @var array<string, string>
     */
    private array $mutationHashToSourceFilePath = [];

    /**
     * Maps mutation hash to the flowId used for its testStarted message.
     * This ensures testFinished uses the same flowId for consistency.
     *
     * @var array<string, string>
     */
    private array $mutationHashToFlowId = [];

    /**
     * The source file path of the currently active test suite.
     * Only one test suite can output at a time.
     */
    private ?string $activeTestSuiteSourceFilePath = null;

    /**
     * The mutation hash of the currently active mutation within the active test suite.
     * Only the first mutation in a suite is "active" - subsequent mutations are buffered.
     */
    private ?string $activeMutationHash = null;

    /**
     * Buffered testStarted messages for test suites that need to wait for the active mutation to finish.
     *
     * @var array<string, list<string>>
     */
    private array $bufferedStartedMessages = [];

    /**
     * Buffered testFinished messages for test suites that need to wait for the active mutation to finish.
     *
     * @var array<string, list<string>>
     */
    private array $bufferedFinishedMessages = [];

    /**
     * Order in which buffered suites were started (for FIFO output).
     *
     * @var list<string>
     */
    private array $bufferedSuiteOrder = [];

    public function __construct(
        private readonly TeamCity $teamcity,
        private readonly LoggerInterface $logger,
        private readonly string $configurationDirPathname,
    ) {
    }

    public function startAnalysis(int $mutationCount): void
    {
    }

    public function startEvaluation(Mutation $mutation): void
    {
        $sourceFilePath = $mutation->getOriginalFilePath();
        $mutationHash = $mutation->getHash();

        // Track the mapping from mutation hash to source file path
        $this->mutationHashToSourceFilePath[$mutationHash] = $sourceFilePath;

        $this->ensureTestSuiteIsTracked($sourceFilePath);

        if ($this->activeTestSuiteSourceFilePath === null) {
            $this->activeTestSuiteSourceFilePath = $sourceFilePath;
            $this->activeMutationHash = $mutationHash;

            $flowId = $this->outputTestSuiteStarted($sourceFilePath);

            $this->mutationHashToFlowId[$mutationHash] = $flowId;
            $this->write(
                $this->teamcity->testStarted(
                    $mutation,
                    $flowId,
                ),
            );
        } elseif ($this->activeTestSuiteSourceFilePath === $sourceFilePath) {
            // Same suite as active - buffer if there's an active mutation, otherwise output
            if ($this->activeMutationHash !== null) {
                // Buffer for later output - use suite flowId for buffered tests
                $flowId = $this->testSuites[$sourceFilePath]['flowId'];
                $this->mutationHashToFlowId[$mutationHash] = $flowId;
                $this->bufferStartedMessage(
                    $sourceFilePath,
                    $this->teamcity->testStarted($mutation, $flowId),
                );
            } else {
                $this->activeMutationHash = $mutationHash;
                $flowId = $this->testSuites[$sourceFilePath]['flowId'];
                $this->mutationHashToFlowId[$mutationHash] = $flowId;
                $this->write($this->teamcity->testStarted($mutation, $flowId));
            }
        } else {
            // Different suite - buffer the message
            $this->ensureSuiteIsBuffered($sourceFilePath);
            $flowId = $this->testSuites[$sourceFilePath]['flowId'];
            $this->mutationHashToFlowId[$mutationHash] = $flowId;
            $this->bufferStartedMessage(
                $sourceFilePath,
                $this->teamcity->testStarted($mutation, $flowId),
            );
        }
    }

    public function finishEvaluation(MutantExecutionResult $executionResult): void
    {
        // Use the mutation hash to look up the correct source file path
        $mutantHash = $executionResult->getMutantHash();
        $sourceFilePath = $this->mutationHashToSourceFilePath[$mutantHash];
        // Use the same flowId that was used for testStarted
        $flowId = $this->mutationHashToFlowId[$mutantHash];

        $isActiveMutation = $sourceFilePath === $this->activeTestSuiteSourceFilePath
            && $mutantHash === $this->activeMutationHash;

        if ($isActiveMutation) {
            // Active mutation - output directly
            $this->write($this->teamcity->testFinished($executionResult, $flowId));
            $this->activeMutationHash = null;
            // Flush buffered messages for the current suite
            $this->flushSuiteBuffer($sourceFilePath);
        } elseif ($sourceFilePath === $this->activeTestSuiteSourceFilePath) {
            // Same suite but not active mutation - buffer
            $this->bufferFinishedMessage(
                $sourceFilePath,
                $this->teamcity->testFinished($executionResult, $flowId),
            );
        } else {
            // Buffered suite - buffer the message
            $this->bufferFinishedMessage(
                $sourceFilePath,
                $this->teamcity->testFinished($executionResult, $flowId),
            );
        }

        $this->evaluatedMutationIdsBySourceFilePath[$sourceFilePath][$mutantHash] = true;

        $this->closeTestSuiteIfAllMutationsWereExecuted($sourceFilePath);
    }

    public function finishMutationGenerationForFile(
        string $sourceFilePath,
        array $mutationIds,
    ): void {
        $this->generatedMutationIdsBySourceFilePath[$sourceFilePath] = $mutationIds;
        $this->evaluatedMutationIdsBySourceFilePath[$sourceFilePath] = array_merge(
            array_fill_keys(
                $mutationIds,
                false,
            ),
            $this->evaluatedMutationIdsBySourceFilePath[$sourceFilePath] ?? [],
        );

        $this->closeTestSuiteIfAllMutationsWereExecuted($sourceFilePath);
    }

    public function finishAnalysis(): void
    {
        Assert::count($this->testSuites, 0);
    }

    private function ensureTestSuiteIsTracked(string $sourceFilePath): void
    {
        if (array_key_exists($sourceFilePath, $this->testSuites)) {
            return;
        }

        $relativeSourceFilePath = Path::makeRelative(
            $sourceFilePath,
            $this->configurationDirPathname,
        );
        $flowId = self::createFlowId($relativeSourceFilePath);

        $this->testSuites[$sourceFilePath] = [
            'name' => $relativeSourceFilePath,
            'flowId' => $flowId,
        ];
    }

    private function outputTestSuiteStarted(string $sourceFilePath): string
    {
        $testSuite = $this->testSuites[$sourceFilePath];

        $this->write(
            $this->teamcity->testSuiteStarted(
                name: $testSuite['name'],
                flowId: $testSuite['flowId'],
            ),
        );

        return $testSuite['flowId'];
    }

    private function ensureSuiteIsBuffered(string $sourceFilePath): void
    {
        if (!array_key_exists($sourceFilePath, $this->bufferedStartedMessages)) {
            $this->bufferedStartedMessages[$sourceFilePath] = [];
            $this->bufferedFinishedMessages[$sourceFilePath] = [];
            $this->bufferedSuiteOrder[] = $sourceFilePath;
        }
    }

    private function bufferStartedMessage(string $sourceFilePath, string $message): void
    {
        $this->bufferedStartedMessages[$sourceFilePath][] = $message;
    }

    private function bufferFinishedMessage(string $sourceFilePath, string $message): void
    {
        $this->bufferedFinishedMessages[$sourceFilePath][] = $message;
    }

    private function flushSuiteBuffer(string $sourceFilePath): void
    {
        // Output started messages first, then finished messages
        // to maintain the correct testStarted -> testFinished order
        $startedMessages = $this->bufferedStartedMessages[$sourceFilePath] ?? [];
        $finishedMessages = $this->bufferedFinishedMessages[$sourceFilePath] ?? [];

        foreach ($startedMessages as $message) {
            $this->write($message);
        }

        foreach ($finishedMessages as $message) {
            $this->write($message);
        }

        $this->bufferedStartedMessages[$sourceFilePath] = [];
        $this->bufferedFinishedMessages[$sourceFilePath] = [];
    }

    private function closeTestSuiteIfAllMutationsWereExecuted(string $sourceFilePath): void
    {
        if (!$this->areAllMutationsOfSourceFileExecuted($sourceFilePath)) {
            return;
        }

        unset($this->evaluatedMutationIdsBySourceFilePath[$sourceFilePath]);

        $testSuite = $this->testSuites[$sourceFilePath];
        unset($this->testSuites[$sourceFilePath]);

        if ($sourceFilePath === $this->activeTestSuiteSourceFilePath) {
            // Close the active suite
            $this->write(
                $this->teamcity->testSuiteFinished(
                    name: $testSuite['name'],
                    flowId: $testSuite['flowId'],
                ),
            );
            $this->activeTestSuiteSourceFilePath = null;
            $this->activeMutationHash = null;

            // Flush the next buffered suite if any
            $this->flushNextBufferedSuite();
        } else {
            // This is a buffered suite - buffer the finish message
            // For suites, we use the started buffer since it should come last
            $this->bufferedStartedMessages[$sourceFilePath][] = $this->teamcity->testSuiteFinished(
                name: $testSuite['name'],
                flowId: $testSuite['flowId'],
            );
        }
    }

    private function flushNextBufferedSuite(): void
    {
        if (count($this->bufferedSuiteOrder) === 0) {
            return;
        }

        $nextSourceFilePath = array_shift($this->bufferedSuiteOrder);
        $startedMessages = $this->bufferedStartedMessages[$nextSourceFilePath] ?? [];
        $finishedMessages = $this->bufferedFinishedMessages[$nextSourceFilePath] ?? [];
        unset($this->bufferedStartedMessages[$nextSourceFilePath]);
        unset($this->bufferedFinishedMessages[$nextSourceFilePath]);

        // Check if this suite is still tracked (it might have been completed while buffered)
        if (array_key_exists($nextSourceFilePath, $this->testSuites)) {
            $this->activeTestSuiteSourceFilePath = $nextSourceFilePath;
            $this->outputTestSuiteStarted($nextSourceFilePath);
        }

        // Output started messages first, then finished messages
        // to maintain the correct testStarted -> testFinished order
        foreach ($startedMessages as $message) {
            $this->write($message);
        }

        foreach ($finishedMessages as $message) {
            $this->write($message);
        }

        // If the suite was already completed (finish message was buffered),
        // it won't be in testSuites anymore, and activeTestSuiteSourceFilePath should be cleared
        if (!array_key_exists($nextSourceFilePath, $this->testSuites)) {
            $this->activeTestSuiteSourceFilePath = null;
            $this->activeMutationHash = null;
            // Recursively flush the next buffered suite
            $this->flushNextBufferedSuite();
        }
    }

    private function areAllMutationsOfSourceFileExecuted(string $sourceFilePath): bool
    {
        if (!array_key_exists($sourceFilePath, $this->generatedMutationIdsBySourceFilePath)) {
            return false;
        }

        $mutationIds = $this->generatedMutationIdsBySourceFilePath[$sourceFilePath];
        $evaluatedMutationsByMutationId = $this->evaluatedMutationIdsBySourceFilePath[$sourceFilePath] ?? [];

        foreach ($mutationIds as $mutationId) {
            $evaluated = $evaluatedMutationsByMutationId[$mutationId] ?? false;

            if (!$evaluated) {
                return false;
            }
        }

        return true;
    }

    private function write(string $messsage): void
    {
        $this->logger->warning($messsage);
    }

    private static function createFlowId(string $value): string
    {
        // Any hash which avoids collision, is fast and deterministic will do.
        return hash('xxh3', $value);
    }
}
