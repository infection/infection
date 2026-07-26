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

use function array_diff;
use function array_fill_keys;
use function array_key_exists;
use function array_keys;
use function array_map;
use function count;
use function implode;
use LogicException;
use function sprintf;
use Webmozart\Assert\Assert;

/**
 * Since we are launching test suites and tests in parallel, we need to do a fair amount of tracking.
 *
 * This service is responsible for doing that state tracking. Once a test suite is closed, all of its
 * related states are cleaned up. This is for both memory efficiency and making it easier that there is
 * no dangling state at the end of the run, which would translate to not everything having been logged
 * correctly.
 *
 * @internal
 */
final class TeamCityLoggerState
{
    /**
     * @var array<string, TestSuite>
     */
    private array $openedTestSuitesBySourceFilePath = [];

    /**
     * @var array<string, TestSuite>
     */
    private array $openedTestSuitesByNodeId = [];

    /**
     * @var array<string, Test>
     */
    private array $openedTestsById = [];

    /**
     * @var array<string, array<string, true>>
     */
    private array $finishedTestIdsAsKeysByTestSuiteNodeId = [];

    /**
     * @var array<array<string, true>>
     */
    private array $remainingTestIdsByTestSuiteSourceFilePath = [];

    public function openTestSuite(TestSuite $suite): void
    {
        $this->openedTestSuitesBySourceFilePath[$suite->sourceFilePath] = $suite;
        $this->openedTestSuitesByNodeId[$suite->nodeId] = $suite;
    }

    public function closeTestSuite(string $sourceFilePath): void
    {
        $testSuite = $this->getTestSuite($sourceFilePath);

        $this->assertAllTestsAreFinished($testSuite);

        unset($this->openedTestSuitesBySourceFilePath[$sourceFilePath]);
        unset($this->openedTestSuitesByNodeId[$testSuite->nodeId]);
        unset($this->remainingTestIdsByTestSuiteSourceFilePath[$sourceFilePath]);
        unset($this->finishedTestIdsAsKeysByTestSuiteNodeId[$testSuite->nodeId]);
    }

    public function findTestSuite(string $sourceFilePath): ?TestSuite
    {
        return $this->openedTestSuitesBySourceFilePath[$sourceFilePath] ?? null;
    }

    public function getTestSuite(string $sourceFilePath): TestSuite
    {
        Assert::keyExists(
            $this->openedTestSuitesBySourceFilePath,
            $sourceFilePath,
            sprintf(
                'No open test suite found for the source file "%s".',
                $sourceFilePath,
            ),
        );

        return $this->openedTestSuitesBySourceFilePath[$sourceFilePath];
    }

    /**
     * @param list<string> $testIds
     */
    public function registerTestsForTestSuite(
        string $sourceFilePath,
        array $testIds,
    ): void {
        $testSuite = $this->findTestSuite($sourceFilePath);

        // Note that the test suite may not be opened yet at this point! So there may not be any
        $finishedTestIds = $testSuite === null
            ? []
            : array_keys($this->finishedTestIdsAsKeysByTestSuiteNodeId[$testSuite->nodeId] ?? []);
        $remainingTestIds = array_diff($testIds, $finishedTestIds);

        $this->remainingTestIdsByTestSuiteSourceFilePath[$sourceFilePath] = array_fill_keys($remainingTestIds, true);
    }

    public function openTest(Test $test): void
    {
        $this->openedTestsById[$test->id] = $test;
    }

    public function closeTest(Test $test): void
    {
        $testSuiteNodeId = $test->parentNodeId;

        Assert::keyExists(
            $this->openedTestSuitesByNodeId,
            $testSuiteNodeId,
            sprintf(
                'Cannot close the test "%s" (nodeId=%s): its test suite nodeId=%s was not opened.',
                $test->name,
                $test->nodeId,
                $testSuiteNodeId,
            ),
        );
        $testSuite = $this->openedTestSuitesByNodeId[$testSuiteNodeId];

        unset($this->openedTestsById[$test->id]);
        $this->finishedTestIdsAsKeysByTestSuiteNodeId[$testSuiteNodeId][$test->id] = true;

        // We may not know the complete list of tests of a test suite. Until we know it, we track
        // the list of finished tests for that test suite.
        if (array_key_exists($testSuite->sourceFilePath, $this->remainingTestIdsByTestSuiteSourceFilePath)) {
            unset($this->remainingTestIdsByTestSuiteSourceFilePath[$testSuite->sourceFilePath][$test->id]);
        }
    }

    public function getTest(string $testId): Test
    {
        return $this->openedTestsById[$testId];
    }

    public function areAllTestsOfTheTestSuiteFinished(string $sourceFilePath): bool
    {
        $testSuiteNodeId = $this->findTestSuite($sourceFilePath)?->nodeId;

        if (
            $testSuiteNodeId === null
            || !array_key_exists($sourceFilePath, $this->remainingTestIdsByTestSuiteSourceFilePath)
        ) {
            return false;
        }

        return count($this->remainingTestIdsByTestSuiteSourceFilePath[$sourceFilePath]) === 0;
    }

    public function assertAllTestSuitesAreClosed(): void
    {
        if (count($this->openedTestSuitesBySourceFilePath) > 0) {
            throw new LogicException(
                sprintf(
                    'Expected all test suites to be closed. Found: %s',
                    implode(
                        ', ',
                        array_map(
                            static fn (TestSuite $suite): string => sprintf(
                                '"%s" (nodeId=%s)',
                                $suite->name,
                                $suite->nodeId,
                            ),
                            $this->openedTestSuitesBySourceFilePath,
                        ),
                    ),
                ),
            );
        }

        // These are for good measure, they should not be possible, hence cannot
        // be tested without extremely artificial tests.
        Assert::count($this->openedTestSuitesByNodeId, 0);
        Assert::count($this->openedTestsById, 0);
        Assert::count($this->remainingTestIdsByTestSuiteSourceFilePath, 0);
        Assert::count($this->finishedTestIdsAsKeysByTestSuiteNodeId, 0);
    }

    private function assertAllTestsAreFinished(TestSuite $testSuite): void
    {
        if (!array_key_exists($testSuite->sourceFilePath, $this->remainingTestIdsByTestSuiteSourceFilePath)) {
            throw new LogicException(
                sprintf(
                    'Cannot close the test suite "%s" (nodeId=%s): its list of tests is not known yet.',
                    $testSuite->name,
                    $testSuite->nodeId,
                ),
            );
        }

        $remainingTestIds = array_keys($this->remainingTestIdsByTestSuiteSourceFilePath[$testSuite->sourceFilePath] ?? []);

        Assert::count(
            $remainingTestIds,
            0,
            sprintf(
                'Found %d opened or non-executed test(s) for the test suite "%s" (nodeId=%s): %s.',
                count($remainingTestIds),
                $testSuite->name,
                $testSuite->nodeId,
                implode(
                    ', ',
                    $remainingTestIds,
                ),
            ),
        );
    }
}
