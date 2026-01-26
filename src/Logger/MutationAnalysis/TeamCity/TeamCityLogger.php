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
use function array_find_key;
use function array_key_exists;
use function array_merge;
use function array_shift;
use function array_values;
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
     * @var array<string, array<string, bool>>
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
    private array $trackedTestSuites = [];

    /**
     * The source file path of the currently active test suite.
     * Only one test suite can output at a time.
     */
    private ?string $activeTestSuiteSourceFilePath = null;

    /**
     * The mutation hash of the currently active mutation within the active test suite.
     * Only the first mutation in a suite is "active" – subsequent mutations are buffered.
     */
    private ?string $activeMutationHash = null;

    /**
     * Buffered testStarted messages for test suites that need to wait for the active mutation to finish.
     * Each entry contains the mutation hash and the formatted message.
     *
     * @var array<string, list<array{hash: string, message: string}>>
     */
    private array $bufferedStartedMessages = [];

    /**
     * Buffered testFinished messages for test suites that need to wait for the active mutation to finish.
     * Each entry contains the mutation hash and the formatted message.
     *
     * @var array<string, list<array{hash: string, message: string}>>
     */
    private array $bufferedFinishedMessages = [];

    /**
     * Order in which buffered suites were started (for FIFO output).
     *
     * @var list<string>
     */
    private array $bufferedSuiteOrder = [];

    /**
     * Buffered testSuiteFinished messages for completed buffered suites.
     *
     * @var array<string, string>
     */
    private array $bufferedSuiteFinishedMessages = [];

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

        $this->ensureTestSuiteIsTracked($sourceFilePath);

        if ($this->activeTestSuiteSourceFilePath === null) {
            $this->activeTestSuiteSourceFilePath = $sourceFilePath;
            $this->activeMutationHash = $mutationHash;

            $flowId = $this->outputTestSuiteStarted($sourceFilePath);
            $this->outputTestStarted($mutation, $flowId);
        } elseif ($this->activeTestSuiteSourceFilePath === $sourceFilePath) {
            $flowId = $this->trackedTestSuites[$sourceFilePath]['flowId'];

            if ($this->activeMutationHash === null) {
                $this->activeMutationHash = $mutationHash;

                $this->outputTestStarted($mutation, $flowId);
            } else {
                $this->bufferTestStarted(
                    $sourceFilePath,
                    $mutation,
                    $flowId,
                );
            }
        } else {
            $this->ensureSuiteIsBuffered($sourceFilePath);

            $flowId = $this->trackedTestSuites[$sourceFilePath]['flowId'];

            $this->bufferTestStarted(
                $sourceFilePath,
                $mutation,
                $flowId,
            );
        }
    }

    public function finishEvaluation(MutantExecutionResult $executionResult): void
    {
        $mutantHash = $executionResult->getMutantHash();
        $sourceFilePath = $executionResult->getOriginalFilePath();
        $flowId = $this->trackedTestSuites[$sourceFilePath]['flowId'];

        $isActiveMutation = $sourceFilePath === $this->activeTestSuiteSourceFilePath
            && $mutantHash === $this->activeMutationHash;
        $isActiveTestSuite = $sourceFilePath === $this->activeTestSuiteSourceFilePath;

        if ($isActiveMutation) {
            $this->outputTestFinished(
                $executionResult,
                $flowId,
            );

            $this->flushTestSuiteBuffer($sourceFilePath);

            $this->closeTestSuiteIfAllMutationsWereExecuted($sourceFilePath);
        } elseif ($isActiveTestSuite) {
            if ($this->activeMutationHash === null) {
                $this->outputTestFinished(
                    $executionResult,
                    $flowId,
                );

                $this->closeTestSuiteIfAllMutationsWereExecuted($sourceFilePath);
            } else {
                $this->bufferFinishedMessage(
                    $sourceFilePath,
                    $executionResult,
                    $flowId,
                );
            }
        } else {
            $this->bufferFinishedMessage(
                $sourceFilePath,
                $executionResult,
                $flowId,
            );
        }
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
        // Safeguard: ensure all messages were flushed.
        Assert::count($this->trackedTestSuites, 0);
        Assert::count($this->bufferedSuiteOrder, 0);
        Assert::count($this->bufferedStartedMessages, 0);
        Assert::count($this->bufferedFinishedMessages, 0);
    }

    private function ensureTestSuiteIsTracked(string $sourceFilePath): void
    {
        if (array_key_exists($sourceFilePath, $this->trackedTestSuites)) {
            return;
        }

        $relativeSourceFilePath = Path::makeRelative(
            $sourceFilePath,
            $this->configurationDirPathname,
        );
        $flowId = self::createFlowId($relativeSourceFilePath);

        $this->trackedTestSuites[$sourceFilePath] = [
            'name' => $relativeSourceFilePath,
            'flowId' => $flowId,
        ];
    }

    private function outputTestSuiteStarted(string $sourceFilePath): string
    {
        $testSuite = $this->trackedTestSuites[$sourceFilePath];

        $this->write(
            $this->teamcity->testSuiteStarted(
                name: $testSuite['name'],
                flowId: $testSuite['flowId'],
            ),
        );

        return $testSuite['flowId'];
    }

    private function outputTestSuiteFinished(
        string $sourceFilePath,
        string $name,
        string $flowId,
    ): void {
        $this->write(
            $this->teamcity->testSuiteFinished(
                name: $name,
                flowId: $flowId,
            ),
        );

        // Safeguard: we don't want to unset entries that were not flushed by
        // accident.
        Assert::count($this->bufferedSuiteOrder[$sourceFilePath] ?? [], 0);
        Assert::count($this->bufferedStartedMessages[$sourceFilePath] ?? [], 0);
        Assert::count($this->bufferedFinishedMessages[$sourceFilePath] ?? [], 0);

        unset($this->bufferedSuiteOrder[$sourceFilePath]);
        unset($this->bufferedStartedMessages[$sourceFilePath]);
        unset($this->bufferedFinishedMessages[$sourceFilePath]);

        $this->activeTestSuiteSourceFilePath = null;
        $this->activeMutationHash = null;
    }

    private function bufferTestSuiteFinished(
        string $sourceFilePath,
        string $name,
        string $flowId,
    ): void {
        $this->bufferedSuiteFinishedMessages[$sourceFilePath] = $this->teamcity->testSuiteFinished(
            name: $name,
            flowId: $flowId,
        );
    }

    private function outputTestStarted(Mutation $mutation, string $flowId): void
    {
        $this->write(
            $this->teamcity->testStarted(
                $mutation,
                $flowId,
            ),
        );
    }

    private function outputTestFinished(
        MutantExecutionResult $executionResult,
        string $flowId,
    ): void {
        $this->write(
            $this->teamcity->testFinished(
                $executionResult,
                $flowId,
            ),
        );

        $this->evaluatedMutationIdsBySourceFilePath[$executionResult->getOriginalFilePath()][$executionResult->getMutantHash()] = true;
        $this->activeMutationHash = null;
    }

    private function ensureSuiteIsBuffered(string $sourceFilePath): void
    {
        if (!array_key_exists($sourceFilePath, $this->bufferedStartedMessages)) {
            $this->bufferedStartedMessages[$sourceFilePath] = [];
            $this->bufferedFinishedMessages[$sourceFilePath] = [];
            $this->bufferedSuiteOrder[] = $sourceFilePath;
        }
    }

    private function bufferTestStarted(
        string $sourceFilePath,
        Mutation $mutation,
        string $flowId,
    ): void {
        $this->bufferedStartedMessages[$sourceFilePath][] = [
            'hash' => $mutation->getHash(),
            'message' => $this->teamcity->testStarted($mutation, $flowId),
        ];
    }

    private function bufferFinishedMessage(
        string $sourceFilePath,
        MutantExecutionResult $executionResult,
        string $flowId,
    ): void {
        $this->bufferedFinishedMessages[$sourceFilePath][] = [
            'hash' => $executionResult->getMutantHash(),
            'message' => $this->teamcity->testFinished($executionResult, $flowId),
        ];
    }

    private function flushTestSuiteBuffer(string $sourceFilePath): void
    {
        // Only output ONE testStarted at a time to maintain proper ordering.
        // Each mutation's testStarted must be followed by its testFinished
        // before the next mutation's testStarted.
        $startedMessages = $this->bufferedStartedMessages[$sourceFilePath] ?? [];

        if (count($startedMessages) === 0) {
            return;
        }

        // Pop the first buffered mutation and make it active
        /** @var array{hash: string, message: string} $nextMutation */
        $nextMutation = array_shift($this->bufferedStartedMessages[$sourceFilePath]);

        $mutationHash = $nextMutation['hash'];
        $this->activeMutationHash = $mutationHash;
        $this->write($nextMutation['message']);

        $this->outputFinishedMessageIfPossible(
            $sourceFilePath,
            $mutationHash,
        );
    }

    // Check if the testFinished for this mutation is already buffered
    private function outputFinishedMessageIfPossible(
        string $sourceFilePath,
        string $mutationHash,
    ): void {
        $finishedMessageKey = $this->findMatchingFinishedMessageKeyInBufferedMessages(
            $sourceFilePath,
            $mutationHash,
        );

        if ($finishedMessageKey === null) {
            return;
        }

        $finishedMessage = $this->bufferedFinishedMessages[$sourceFilePath][$finishedMessageKey];

        $this->write($finishedMessage['message']);
        $this->evaluatedMutationIdsBySourceFilePath[$sourceFilePath][$mutationHash] = true;
        $this->activeMutationHash = null;

        $this->removeAndReindexRemainingFinishedKeys($sourceFilePath, $finishedMessageKey);

        $this->flushTestSuiteBuffer($sourceFilePath);
    }

    private function removeAndReindexRemainingFinishedKeys(
        string $sourceFilePath,
        int $finishedMessageKey,
    ): void {
        $remaining = $this->bufferedFinishedMessages[$sourceFilePath];
        unset($remaining[$finishedMessageKey]);

        $this->bufferedFinishedMessages[$sourceFilePath] = array_values($remaining);
    }

    private function findMatchingFinishedMessageKeyInBufferedMessages(
        string $sourceFilePath,
        string $mutationHash,
    ): ?int {
        return array_find_key(
            $this->bufferedFinishedMessages[$sourceFilePath] ?? [],
            static fn (array $message) => $mutationHash === $message['hash'],
        );
    }

    private function closeTestSuiteIfAllMutationsWereExecuted(string $sourceFilePath): void
    {
        if (!$this->areAllMutationsOfSourceFileExecuted($sourceFilePath)) {
            return;
        }

        unset($this->evaluatedMutationIdsBySourceFilePath[$sourceFilePath]);

        $testSuite = $this->trackedTestSuites[$sourceFilePath];
        unset($this->trackedTestSuites[$sourceFilePath]);

        if ($sourceFilePath === $this->activeTestSuiteSourceFilePath) {
            $this->outputTestSuiteFinished(
                $sourceFilePath,
                $testSuite['name'],
                $testSuite['flowId'],
            );

            $this->flushNextBufferedSuiteIfAny();
        } else {
            $this->bufferTestSuiteFinished(
                $sourceFilePath,
                $testSuite['name'],
                $testSuite['flowId'],
            );
        }
    }

    private function flushNextBufferedSuiteIfAny(): void
    {
        if (count($this->bufferedSuiteOrder) === 0) {
            return;
        }

        $nextSourceFilePath = array_shift($this->bufferedSuiteOrder);

        // Check if this suite is still tracked (it might have been completed while buffered)
        if (array_key_exists($nextSourceFilePath, $this->trackedTestSuites)) {
            $this->activeTestSuiteSourceFilePath = $nextSourceFilePath;

            $this->outputTestSuiteStarted($nextSourceFilePath);
            $this->flushTestSuiteBuffer($nextSourceFilePath);
        } else {
            $this->outputCompletedTestSuite($nextSourceFilePath);
        }
    }

    // This is for when the test suite was completed while buffered. In this case,
    // we can output all of its messages at once.
    private function outputCompletedTestSuite(string $sourceFilePath): void
    {
        $startedMessages = $this->bufferedStartedMessages[$sourceFilePath];
        $finishedMessages = $this->bufferedFinishedMessages[$sourceFilePath];
        $suiteFinishedMessage = $this->bufferedSuiteFinishedMessages[$sourceFilePath];
        unset($this->bufferedStartedMessages[$sourceFilePath]);
        unset($this->bufferedFinishedMessages[$sourceFilePath]);
        unset($this->bufferedSuiteFinishedMessages[$sourceFilePath]);

        $finishedMessagesByHash = self::mapIndexMessagesByHash($finishedMessages);

        // Output paired: started then finished for each mutation
        foreach ($startedMessages as ['hash' => $hash, 'message' => $startMessage]) {
            $finishedMessage = $finishedMessagesByHash[$hash];
            unset($finishedMessagesByHash[$hash]);

            $this->write($startMessage);
            $this->write($finishedMessage);
        }

        $this->write($suiteFinishedMessage);

        $this->activeTestSuiteSourceFilePath = null;
        $this->activeMutationHash = null;

        $this->flushNextBufferedSuiteIfAny();
    }

    /**
     * @param array{hash: string, message: string} $records
     *
     * @return array<string, string>
     */
    private static function mapIndexMessagesByHash(array $records): array
    {
        $indexedMessages = [];

        foreach ($records as $record) {
            $indexedMessages[$record['hash']] = $record['message'];
        }

        return $indexedMessages;
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
