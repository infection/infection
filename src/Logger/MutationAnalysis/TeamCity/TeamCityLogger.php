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

use function hash;
use Infection\Framework\Iterable\IterableCounter;
use Infection\Logger\MutationAnalysis\MutationAnalysisLogger;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutation\Mutation;
use Psr\Log\LoggerInterface;

/**
 * @phpstan-type MutationRecord = array{hash: string, message: string}
 *
 * @internal
 */
final readonly class TeamCityLogger implements MutationAnalysisLogger
{
    public function __construct(
        private TeamCity $teamcity,
        private TeamCityLoggerState $state,
        private LoggerInterface $logger,
        private string $configurationDirPathname,
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

        $testSuiteNodeId = $this->startTestSuiteIfNotStarted($sourceFilePath);

        $this->startTest($mutation, $testSuiteNodeId);
    }

    public function finishEvaluation(MutantExecutionResult $executionResult): void
    {
        $sourceFilePath = $executionResult->getOriginalFilePath();

        $test = $this->state->getTest($executionResult->getMutantHash());

        $this->finishTest($test, $executionResult);

        $this->finishTestSuiteIfAllMutationsWereExecuted($sourceFilePath);
    }

    public function finishMutationGenerationForFile(
        string $sourceFilePath,
        array $mutationIds,
    ): void {
        $this->state->registerTestsForTestSuite(
            sourceFilePath: $sourceFilePath,
            testIds: $mutationIds,
        );

        $this->finishTestSuiteIfAllMutationsWereExecuted($sourceFilePath);
    }

    public function finishAnalysis(): void
    {
        $this->state->assertAllTestSuitesAreClosed();
    }

    private function startTestSuiteIfNotStarted(string $sourceFilePath): string
    {
        return $this->state->findTestSuite($sourceFilePath)->nodeId
            ?? $this->startTestSuite($sourceFilePath)->nodeId;
    }

    private function startTestSuite(string $sourceFilePath): TestSuite
    {
        $testSuite = TestSuite::create(
            $sourceFilePath,
            $this->configurationDirPathname,
        );

        $this->state->openTestSuite($testSuite);
        $this->write(
            $this->teamcity->testSuiteStarted($testSuite),
        );

        return $testSuite;
    }

    private function finishTestSuite(string $sourceFilePath): void
    {
        $testSuite = $this->state->getTestSuite($sourceFilePath);
        $this->state->closeTestSuite($sourceFilePath);

        $this->write(
            $this->teamcity->testSuiteFinished($testSuite),
        );
    }

    private function startTest(Mutation $mutation, string $parentNodeId): void
    {
        $test = Test::create($mutation, $parentNodeId);

        $this->state->openTest($test);
        $this->write(
            $this->teamcity->testStarted($test),
        );
    }

    private function finishTest(
        Test $test,
        MutantExecutionResult $executionResult,
    ): void {
        $this->write(
            $this->teamcity->testFinished($test, $executionResult),
        );

        $this->state->closeTest($test);
    }

    private function finishTestSuiteIfAllMutationsWereExecuted(string $sourceFilePath): void
    {
        if ($this->state->areAllTestsOfTheTestSuiteFinished($sourceFilePath)) {
            $this->finishTestSuite($sourceFilePath);
        }
    }

    private function write(string $messsage): void
    {
        $this->logger->warning($messsage);
    }
}
