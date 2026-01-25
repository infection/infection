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
use function array_reverse;
use const DIRECTORY_SEPARATOR;
use function explode;
use function hash;
use Infection\Logger\MutationAnalysis\MutationAnalysisLogger;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutation\Mutation;
use Psr\Log\LoggerInterface;
use function sprintf;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;

// TODO: explain somewhere the concept of TestSuite and Test for TeamCity
final class TeamCityLogger implements MutationAnalysisLogger
{
    private const MILLISECONDS_PER_SECOND = 1000;

    /**
     * @var array<string, array<string, true>>
     */
    private array $evaluatedMutationIdsBySourceFilePath = [];

    /**
     * @var array<string, array{'name': string, 'flowId': string}> The index is the unchanged (absolute) source path of the source file.
     */
    private array $openTestSuites = [];

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
        // Open the test suite if not already opened
        $flowId = $this->startTestSuiteIfNecessary(
            $mutation->getOriginalFilePath(),
        );

        $this->write(
            $this->teamcity->testStarted($mutation, $flowId),
        );
    }

    public function finishEvaluation(MutantExecutionResult $executionResult): void
    {
        $sourceFilePath = $executionResult->getOriginalFilePath();
        $flowId = $this->openTestSuites[$sourceFilePath]['flowId'];

        $this->write(
            $this->teamcity->testFinished(
                $executionResult,
                $flowId,
            ),
        );

        $this->evaluatedMutationIdsBySourceFilePath[$sourceFilePath][$executionResult->getMutantHash()] = true;

        // $this->closeTestSuiteIfAllMutationsWereExecuted($sourceFilePath);
    }

    public function finishMutationGenerationForFile(
        string $sourceFilePath,
        array $mutationIds,
    ): void {
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
        Assert::count($this->openTestSuites, 0);
    }

    private function startTestSuiteIfNecessary(string $sourceFilePath): string
    {
        if (array_key_exists($sourceFilePath, $this->openTestSuites)) {
            return $this->openTestSuites[$sourceFilePath]['flowId'];
        }

        $relativeSourceFilePath = Path::makeRelative(
            $sourceFilePath,
            $this->configurationDirPathname,
        );
        $flowId = self::createFlowId($relativeSourceFilePath);

        $this->openTestSuites[$sourceFilePath] = [
            'name' => $relativeSourceFilePath,
            'flowId' => $flowId,
        ];

        $this->write(
            $this->teamcity->testSuiteStarted(
                // TODO: add test to showcase that this is needed: a test suite name must be unique
                name: $relativeSourceFilePath,
                flowId: $flowId,
            ),
        );

        return $flowId;
    }

    private function closeTestSuiteIfAllMutationsWereExecuted(string $sourceFilePath): void
    {
        if (!$this->areAllMutationsOfSourceFileExecuted($sourceFilePath)) {
            return;
        }

        unset($this->evaluatedMutationIdsBySourceFilePath[$sourceFilePath]);

        $testSuite = $this->openTestSuites[$sourceFilePath];
        unset($this->openTestSuites[$sourceFilePath]);

        $this->write(
            $this->teamcity->testSuiteFinished(
                name: $testSuite['name'],
                flowId: $testSuite['flowId'],
            ),
        );
    }

    private function areAllMutationsOfSourceFileExecuted(string $sourceFilePath): bool
    {
        $evaluatedMutations = $this->evaluatedMutationIdsBySourceFilePath[$sourceFilePath] ?? [];

        foreach ($evaluatedMutations as $evaluated) {
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

    private static function createMutationFlowId(Mutation $mutation): string
    {
        // TODO: the mutation hash would be better but is too big!
        return self::createFlowId($mutation->getHash());
    }

    private function processResult(MutantExecutionResult $result): void
    {
        // If the current path is:
        // /path/to/project/src/Differ/DiffColorizer.php
        // and the current working dir:
        // /path/to/project/sub-dir
        // Then the relative path would be:
        // '../src/Differ/DiffColorizer.php'
        // so '..' does appear at the top hierarchy... But I think it's ok.
        // Either we take the current working dir as the base path or the config
        // file.
        $filePath = Path::makeRelative(
            $result->getOriginalFilePath(),
            $this->configurationDirPathname,
        );
        $pathSegments = $this->getPathSegments($filePath);

        $this->adjustSuiteHierarchy($pathSegments);
        $this->emitTestResult($result);
    }

    /**
     * @return list<string>
     */
    private function getPathSegments(string $filePath): array
    {
        return explode(DIRECTORY_SEPARATOR, $filePath);
    }

    private function closeAllSuites(): void
    {
        foreach (array_reverse($this->openSuites) as $suiteName) {
            $this->write(
                $this->teamcity->testSuiteFinished($suiteName),
            );
        }

        $this->openSuites = [];
    }

    private function emitTestResult(MutantExecutionResult $result): void
    {
        $testName = $this->getMutantTestName($result);
        $durationMs = (int) ($result->getProcessRuntime() * self::MILLISECONDS_PER_SECOND);

        $this->write(
            $this->teamcity->testStarted($testName),
        );

        match ($result->getDetectionStatus()) {
            DetectionStatus::KILLED_BY_TESTS,
            DetectionStatus::KILLED_BY_STATIC_ANALYSIS => $this->write(
                $this->teamcity->testFinished($testName, $durationMs),
            ),
            DetectionStatus::ESCAPED => $this->emitEscapedMutant($testName, $result, $durationMs),
            DetectionStatus::TIMED_OUT => $this->emitTimedOutMutant($testName, $result, $durationMs),
            DetectionStatus::ERROR,
            DetectionStatus::SYNTAX_ERROR => $this->emitErrorMutant($testName, $result, $durationMs),
            DetectionStatus::SKIPPED,
            DetectionStatus::NOT_COVERED,
            DetectionStatus::IGNORED => $this->emitIgnoredMutant($testName, $result, $durationMs),
        };
    }

    private function getMutantTestName(MutantExecutionResult $result): string
    {
        return sprintf(
            '%s (L%d) %s',
            $result->getMutatorName(),
            $result->getOriginalStartingLine(),
            $result->getMutantHash(),
        );
    }

    private function emitEscapedMutant(
        string $testName,
        MutantExecutionResult $result,
        int $durationMs,
    ): void {
        $this->write(
            $this->teamcity->testFailed(
                $testName,
                'Mutant escaped',
                $result->getMutantDiff(),
            ),
        );

        $this->write(
            $this->teamcity->testFinished($testName, $durationMs),
        );
    }

    private function emitTimedOutMutant(
        string $testName,
        MutantExecutionResult $result,
        int $durationMs,
    ): void {
        $this->write(
            $this->teamcity->testFailed(
                $testName,
                'Mutant timed out',
                $result->getMutantDiff(),
            ),
        );

        $this->write(
            $this->teamcity->testFinished($testName, $durationMs),
        );
    }

    private function emitErrorMutant(
        string $testName,
        MutantExecutionResult $result,
        int $durationMs,
    ): void {
        $this->write(
            $this->teamcity->testFailed(
                $testName,
                sprintf('Mutant caused %s', $result->getDetectionStatus()->value),
                $result->getProcessOutput(),
            ),
        );

        $this->write(
            $this->teamcity->testFinished($testName, $durationMs),
        );
    }

    private function emitIgnoredMutant(
        string $testName,
        MutantExecutionResult $result,
        int $durationMs,
    ): void {
        $this->write(
            $this->teamcity->testIgnored(
                $testName,
                sprintf('Mutant %s', $result->getDetectionStatus()->value),
            ),
        );

        $this->write(
            $this->teamcity->testFinished($testName, $durationMs),
        );
    }
}
