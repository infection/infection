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

use EmptyIterator;
use Infection\Framework\Iterable\IterableCounter;
use function array_key_exists;
use function array_map;
use function count;
use function hash;
use function implode;
use Infection\Logger\MutationAnalysis\MutationAnalysisLogger;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutation\Mutation;
use Psr\Log\LoggerInterface;
use function sprintf;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;

// TODO: explain somewhere the concept of TestSuite and Test for TeamCity

/**
 * @phpstan-type TestSuite = array{name: string, flowId: string}
 * @phpstan-type Test = array{flowId: string, parentFlowId: string}
 * @phpstan-type MutationRecord = array{hash: string, message: string}
 */
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
     * @var array<string, list<string>> The index is the unchanged (absolute) source path of the source file.
     */
    private array $generatedMutationHashesBySourceFilePath = [];

    /**
     * @var array<string, TestSuite> The index is the unchanged (absolute) source path of the source file.
     */
    private array $openedTestSuites = [];

    /**
     * @var array<string, array<string, Test>> The index is the unchanged (absolute) source path of
     *                                         the source file and the inner array key is the mutation hash.
     */
    private array $openedTests = [];

    public function __construct(
        private readonly TeamCity $teamcity,
        private readonly LoggerInterface $logger,
        private readonly string $configurationDirPathname,
    ) {
    }

    public function startAnalysis(int $mutationCount): void
    {
        if ($mutationCount !== IterableCounter::UNKNOWN_COUNT) {
            $this->write(
                $this->teamcity->testCount($mutationCount),
            );
        }
    }

    public function startEvaluation(Mutation $mutation): void
    {
        $sourceFilePath = $mutation->getOriginalFilePath();

        $testSuiteFlowId = $this->startTestSuiteIfNotStarted($sourceFilePath);

        $this->startTest($mutation, $testSuiteFlowId);
    }

    public function finishEvaluation(MutantExecutionResult $executionResult): void
    {
        $sourceFilePath = $executionResult->getOriginalFilePath();
        [
            'flowId' => $flowId,
            'parentFlowId' => $parentFlowId,
        ] = $this->openedTests[$sourceFilePath][$executionResult->getMutantHash()];

        $this->finishTest(
            $executionResult,
            $flowId,
            $parentFlowId,
        );

        $this->finishTestSuiteIfAllMutationsWereExecuted($sourceFilePath);
    }

    public function finishMutationGenerationForFile(
        string $sourceFilePath,
        array $mutationIds,
    ): void {
        $this->generatedMutationHashesBySourceFilePath[$sourceFilePath] = $mutationIds;

        $this->finishTestSuiteIfAllMutationsWereExecuted($sourceFilePath);
    }

    public function finishAnalysis(): void
    {
        Assert::count($this->openedTestSuites, 0);
        Assert::count($this->openedTests, 0);
        Assert::count($this->generatedMutationHashesBySourceFilePath, 0);
    }

    /**
     * @return string TestSuite flowID.
     */
    private function startTestSuiteIfNotStarted(string $sourceFilePath): string
    {
        return array_key_exists($sourceFilePath, $this->openedTestSuites)
            ? $this->openedTestSuites[$sourceFilePath]['flowId']
            : $this->startTestSuite(
                $sourceFilePath,
            );
    }

    /**
     * @return string TestSuite flowID.
     */
    private function startTestSuite(string $sourceFilePath): string
    {
        $relativeSourceFilePath = Path::makeRelative(
            $sourceFilePath,
            $this->configurationDirPathname,
        );
        $flowId = self::createFlowId($relativeSourceFilePath);

        $this->openedTestSuites[$sourceFilePath] = [
            'name' => $relativeSourceFilePath,
            'flowId' => $flowId,
        ];

        $this->write(
            $this->teamcity->testSuiteStarted(
                name: $relativeSourceFilePath,
                flowId: $flowId,
            ),
        );

        return $flowId;
    }

    private function finishTestSuite(string $sourceFilePath): void
    {
        $testSuite = $this->openedTestSuites[$sourceFilePath];

        $this->write(
            $this->teamcity->testSuiteFinished(
                name: $testSuite['name'],
                flowId: $testSuite['flowId'],
            ),
        );

        unset($this->generatedMutationHashesBySourceFilePath[$sourceFilePath]);
        unset($this->evaluatedMutationIdsBySourceFilePath[$sourceFilePath]);
        unset($this->openedTestSuites[$sourceFilePath]);

        $this->ensureAllTestsAreFinished($testSuite['name'], $sourceFilePath);
    }

    private function ensureAllTestsAreFinished(
        string $testSuiteName,
        string $sourceFilePath,
    ): void {
        $openedTests = $this->openedTests[$sourceFilePath];

        Assert::count(
            $openedTests,
            0,
            sprintf(
                'Found %d opened tests for the test suite "%s": %s.',
                count($openedTests),
                $testSuiteName,
                implode(
                    ', ',
                    array_map(
                        static fn (array $test) => $test['flowId'],
                        $openedTests,
                    ),
                ),
            ),
        );

        unset($this->openedTests[$sourceFilePath]);
    }

    private function startTest(Mutation $mutation, string $parentFlowId): void
    {
        // The Mutation hash is too long to be suitable to be a flowId.
        $flowId = self::createFlowId($mutation->getHash());

        $this->write(
            $this->teamcity->testStarted(
                $mutation,
                $flowId,
                $parentFlowId,
            ),
        );

        $this->openedTests[$mutation->getOriginalFilePath()][$mutation->getHash()] = [
            'flowId' => $flowId,
            'parentFlowId' => $parentFlowId,
        ];
    }

    private function finishTest(
        MutantExecutionResult $executionResult,
        string $flowId,
        string $parentFlowId,
    ): void {
        $this->write(
            $this->teamcity->testFinished(
                $executionResult,
                $flowId,
                $parentFlowId,
            ),
        );

        unset($this->openedTests[$executionResult->getOriginalFilePath()][$executionResult->getMutantHash()]);
        $this->evaluatedMutationIdsBySourceFilePath[$executionResult->getOriginalFilePath()][$executionResult->getMutantHash()] = true;
    }

    private function finishTestSuiteIfAllMutationsWereExecuted(string $sourceFilePath): void
    {
        if (!$this->areAllMutationsOfSourceFileExecuted($sourceFilePath)) {
            return;
        }

        $this->finishTestSuite($sourceFilePath);
    }

    private function areAllMutationsOfSourceFileExecuted(string $sourceFilePath): bool
    {
        if (!array_key_exists($sourceFilePath, $this->generatedMutationHashesBySourceFilePath)) {
            return false;
        }

        $mutationIds = $this->generatedMutationHashesBySourceFilePath[$sourceFilePath];
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
