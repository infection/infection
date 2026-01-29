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

namespace Infection\Tests\Logger\MutationAnalysis\TeamCity;

use Infection\Logger\MutationAnalysis\TeamCity\TeamCityLoggerState;
use Infection\Logger\MutationAnalysis\TeamCity\Test;
use Infection\Logger\MutationAnalysis\TeamCity\TestSuite;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TeamCityLoggerState::class)]
final class TeamCityLoggerStateTest extends TestCase
{
    public function test_it_cannot_close_a_test_suite_if_we_do_not_know_all_of_its_tests(): void
    {
        $state = new TeamCityLoggerState();

        $testSuite = new TestSuite(
            '/path/to/project/src/Infrastructure/Http/Action/Greet.php',
            'src/Infrastructure/Http/Action/Greet.php',
            'TS1',
        );

        $testA = new Test(
            'mutationId1',
            'MutatorName (mutationId1)',
            'TA',
            $testSuite->nodeId,
        );

        $state->openTestSuite($testSuite);
        $state->openTest($testA);
        $state->closeTest($testA);

        $this->expectExceptionMessage(
            'Cannot close the test suite "src/Infrastructure/Http/Action/Greet.php" (nodeId=TS1): its list of tests is not known yet.',
        );

        $state->closeTestSuite($testSuite->sourceFilePath);
    }

    public function test_it_cannot_close_a_test_suite_if_there_is_still_non_executed_tests(): void
    {
        $state = new TeamCityLoggerState();

        $testSuite = new TestSuite(
            '/path/to/project/src/Infrastructure/Http/Action/Greet.php',
            'src/Infrastructure/Http/Action/Greet.php',
            'TS1',
        );

        $testA = new Test(
            'mutationId1',
            'MutatorName (mutationId1)',
            'TA',
            $testSuite->nodeId,
        );

        $testB = new Test(
            'mutationId2',
            'MutatorName (mutationId2)',
            'T2',
            $testSuite->nodeId,
        );

        $state->openTestSuite($testSuite);
        $state->openTest($testA);
        $state->closeTest($testA);
        $state->registerTestsForTestSuite(
            $testSuite->sourceFilePath,
            [$testA->id, $testB->id],
        );

        $this->expectExceptionMessage(
            'Found 1 opened or non-executed test(s) for the test suite "src/Infrastructure/Http/Action/Greet.php" (nodeId=TS1): mutationId2.',
        );

        $state->closeTestSuite($testSuite->sourceFilePath);
    }

    public function test_it_cannot_close_a_test_suite_if_it_still_has_unfinished_tests(): void
    {
        $state = new TeamCityLoggerState();

        $testSuite = new TestSuite(
            '/path/to/project/src/Infrastructure/Http/Action/Greet.php',
            'src/Infrastructure/Http/Action/Greet.php',
            'TS1',
        );

        $testA = new Test(
            'mutationId1',
            'MutatorName (mutationId1)',
            'TA',
            $testSuite->nodeId,
        );

        $testB = new Test(
            'mutationId2',
            'MutatorName (mutationId2)',
            'T2',
            $testSuite->nodeId,
        );

        $state->openTestSuite($testSuite);
        $state->openTest($testA);
        $state->closeTest($testA);
        $state->openTest($testB);
        $state->registerTestsForTestSuite(
            $testSuite->sourceFilePath,
            [$testA->id, $testB->id],
        );

        $this->expectExceptionMessage(
            'Found 1 opened or non-executed test(s) for the test suite "src/Infrastructure/Http/Action/Greet.php" (nodeId=TS1): mutationId2.',
        );

        $state->closeTestSuite($testSuite->sourceFilePath);
    }

    public function test_it_cannot_close_a_test_suite_that_was_not_opened(): void
    {
        $state = new TeamCityLoggerState();

        $testSuite = new TestSuite(
            '/path/to/project/src/Infrastructure/Http/Action/Greet.php',
            'src/Infrastructure/Http/Action/Greet.php',
            'TS1',
        );

        $this->expectExceptionMessage(
            'No open test suite found for the source file "/path/to/project/src/Infrastructure/Http/Action/Greet.php".',
        );

        $state->closeTestSuite($testSuite->sourceFilePath);
    }

    public function test_it_cannot_fails_to_assert_that_all_test_suites_are_closed_if_one_is_opened(): void
    {
        $state = new TeamCityLoggerState();

        $testSuite = new TestSuite(
            '/path/to/project/src/Infrastructure/Http/Action/Greet.php',
            'src/Infrastructure/Http/Action/Greet.php',
            'TS1',
        );

        $state->openTestSuite($testSuite);

        $this->expectExceptionMessage(
            'Expected all test suites to be closed. Found: "src/Infrastructure/Http/Action/Greet.php" (nodeId=TS1)',
        );

        $state->assertAllTestSuitesAreClosed();
    }

    public function test_it_cannot_close_a_test_that_was_not_opened(): void
    {
        $state = new TeamCityLoggerState();

        $testSuite = new TestSuite(
            '/path/to/project/src/Infrastructure/Http/Action/Greet.php',
            'src/Infrastructure/Http/Action/Greet.php',
            'TS1',
        );

        $testA = new Test(
            'mutationId1',
            'MutatorName (mutationId1)',
            'TA',
            $testSuite->nodeId,
        );

        $this->expectExceptionMessage(
            'Cannot close the test "MutatorName (mutationId1)" (nodeId=TA): its test suite nodeId=TS1 was not opened.',
        );

        $state->closeTest($testA);
    }
}
