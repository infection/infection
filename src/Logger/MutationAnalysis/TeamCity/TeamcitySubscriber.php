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

use Infection\Event\MutantProcessWasFinished;
use Infection\Event\MutationTestingWasFinished;
use Infection\Event\MutationTestingWasStarted;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Telemetry\Subscriber\GodSubscriber;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use function array_reverse;
use function array_slice;
use function count;
use function explode;
use function file_put_contents;
use function fopen;
use function min;
use function sprintf;
use const DIRECTORY_SEPARATOR;

/**
 * @internal
 */
final class TeamcitySubscriber extends GodSubscriber
{
    private const MILLISECONDS_PER_SECOND = 1000;

    private const DEBUG_LOG = __DIR__ . '/../../../var/teamcity.log';

    private readonly OutputInterface $output;

    private readonly OutputInterface $debugOutput;

    /**
     * @var list<string> Currently open test suite names (forming a path hierarchy)
     */
    private array $openSuites = [];

    public function __construct(
        OutputInterface $output,
        private readonly TeamCity $teamcity,
    ) {
        file_put_contents(self::DEBUG_LOG, '');

        $this->output = new DuplicatedOutput(
            $output,
            new StreamOutput(fopen(self::DEBUG_LOG, 'w')),
        );
    }

    public function onMutationTestingWasStarted(MutationTestingWasStarted $event): void
    {
        parent::onMutationTestingWasStarted($event);

        $this->output->write(
            $this->teamcity->testCount($event->getMutationCount()),
        );
    }

    public function onMutantProcessWasFinished(MutantProcessWasFinished $event): void
    {
        parent::onMutantProcessWasFinished($event);

        $result = $event->getExecutionResult();
        $this->processResult($result);
    }

    public function onMutationTestingWasFinished(MutationTestingWasFinished $event): void
    {
        parent::onMutationTestingWasFinished($event);

        $this->closeAllSuites();
    }

    protected function log(object $event): void
    {
        // No-op: we handle specific events directly
    }

    private function processResult(MutantExecutionResult $result): void
    {
        $filePath = $result->getOriginalFilePath();
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

    /**
     * @param list<string> $newPathSegments
     */
    private function adjustSuiteHierarchy(array $newPathSegments): void
    {
        // Find the common prefix length between current open suites and new path
        $commonPrefixLength = 0;
        $minLength = min(count($this->openSuites), count($newPathSegments));

        for ($i = 0; $i < $minLength; ++$i) {
            if ($this->openSuites[$i] === $newPathSegments[$i]) {
                ++$commonPrefixLength;
            } else {
                break;
            }
        }

        // Close suites that are no longer in the path (in reverse order)
        $suitesToClose = array_slice($this->openSuites, $commonPrefixLength);

        foreach (array_reverse($suitesToClose) as $suiteName) {
            $this->output->write(
                $this->teamcity->testSuiteFinished($suiteName),
            );
        }

        // Open new suites that need to be opened
        $suitesToOpen = array_slice($newPathSegments, $commonPrefixLength);

        foreach ($suitesToOpen as $suiteName) {
            $this->output->write(
                $this->teamcity->testSuiteStarted($suiteName),
            );
        }

        $this->openSuites = $newPathSegments;
    }

    private function closeAllSuites(): void
    {
        foreach (array_reverse($this->openSuites) as $suiteName) {
            $this->output->write(
                $this->teamcity->testSuiteFinished($suiteName),
            );
        }

        $this->openSuites = [];
    }

    private function emitTestResult(MutantExecutionResult $result): void
    {
        $testName = $this->getMutantTestName($result);
        $durationMs = (int) ($result->getProcessRuntime() * self::MILLISECONDS_PER_SECOND);

        $this->output->write(
            $this->teamcity->testStarted($testName),
        );

        match ($result->getDetectionStatus()) {
            DetectionStatus::KILLED_BY_TESTS,
            DetectionStatus::KILLED_BY_STATIC_ANALYSIS => $this->output->write(
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
        $this->output->write(
            $this->teamcity->testFailed(
                $testName,
                'Mutant escaped',
                $result->getMutantDiff(),
            ),
        );

        $this->output->write(
            $this->teamcity->testFinished($testName, $durationMs),
        );
    }

    private function emitTimedOutMutant(
        string $testName,
        MutantExecutionResult $result,
        int $durationMs,
    ): void {
        $this->output->write(
            $this->teamcity->testFailed(
                $testName,
                'Mutant timed out',
                $result->getMutantDiff(),
            ),
        );

        $this->output->write(
            $this->teamcity->testFinished($testName, $durationMs),
        );
    }

    private function emitErrorMutant(
        string $testName,
        MutantExecutionResult $result,
        int $durationMs,
    ): void {
        $this->output->write(
            $this->teamcity->testFailed(
                $testName,
                sprintf('Mutant caused %s', $result->getDetectionStatus()->value),
                $result->getProcessOutput(),
            ),
        );

        $this->output->write(
            $this->teamcity->testFinished($testName, $durationMs),
        );
    }

    private function emitIgnoredMutant(
        string $testName,
        MutantExecutionResult $result,
        int $durationMs,
    ): void {
        $this->output->write(
            $this->teamcity->testIgnored(
                $testName,
                sprintf('Mutant %s', $result->getDetectionStatus()->value),
            ),
        );

        $this->output->write(
            $this->teamcity->testFinished($testName, $durationMs),
        );
    }
}
