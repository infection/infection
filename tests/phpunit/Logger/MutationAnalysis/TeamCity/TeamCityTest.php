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

use Infection\Logger\MutationAnalysis\TeamCity\MessageName;
use Infection\Logger\MutationAnalysis\TeamCity\TeamCity;
use Infection\Logger\MutationAnalysis\TeamCity\Test;
use Infection\Logger\MutationAnalysis\TeamCity\TestSuite;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutator\Boolean\LogicalAnd as LogicalAndMutator;
use Infection\Testing\MutatorName;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TeamCity::class)]
#[CoversClass(TestSuite::class)]
#[CoversClass(Test::class)]
final class TeamCityTest extends TestCase
{
    private TeamCity $teamcity;

    protected function setUp(): void
    {
        $this->teamcity = new TeamCity(timeoutsAsEscaped: false);
    }

    /**
     * @param string|array<non-empty-string|int, string|int|float> $valueOrAttributes
     */
    #[DataProvider('messageProvider')]
    public function test_it_can_write_a_message(
        MessageName $messageName,
        string|array $valueOrAttributes,
        string $expected,
    ): void {
        $actual = $this->teamcity->write(
            $messageName,
            $valueOrAttributes,
        );

        $this->assertSame($expected, $actual);
    }

    public static function messageProvider(): iterable
    {
        yield 'single-attribute message' => [
            MessageName::TEST_COUNT,
            'value',
            "##teamcity[testCount 'value']\n",
        ];

        yield 'multiple-attribute message' => [
            MessageName::TEST_COUNT,
            ['name1' => 'value1', 'name2' => 'value2'],
            "##teamcity[testCount name1='value1' name2='value2']\n",
        ];

        yield '[escape] apostrophe' => [
            MessageName::TEST_COUNT,
            "'",
            "##teamcity[testCount '|'']\n",
        ];

        yield '[escape] line feed' => [
            MessageName::TEST_COUNT,
            "\n",
            "##teamcity[testCount '|n']\n",
        ];

        yield '[escape] carriage return' => [
            MessageName::TEST_COUNT,
            "\r",
            "##teamcity[testCount '|r']\n",
        ];

        yield '[escape] vertical bar' => [
            MessageName::TEST_COUNT,
            '|',
            "##teamcity[testCount '||']\n",
        ];

        yield '[escape] opening bracket' => [
            MessageName::TEST_COUNT,
            '[',
            "##teamcity[testCount '|[']\n",
        ];

        yield '[escape] closing bracket' => [
            MessageName::TEST_COUNT,
            ']',
            "##teamcity[testCount '|]']\n",
        ];

        yield '[escape] message with escaped characters' => [
            MessageName::TEST_COUNT,
            '\'\u99AA[||]\u00FF',
            "##teamcity[testCount '|'|0x99AA|[|||||]|0x00FF']\n",
        ];

        yield '[escape] numeric value (int)' => [
            MessageName::TEST_COUNT,
            ['count' => 42],
            "##teamcity[testCount count='42']\n",
        ];

        yield '[escape] numeric value (float)' => [
            MessageName::TEST_COUNT,
            ['duration' => 3.14],
            "##teamcity[testCount duration='3.14']\n",
        ];
    }

    #[DataProvider('executionResultProvider')]
    public function test_it_can_map_the_execution_result_to_a_finished_test(
        bool $timeoutsAsEscaped,
        Test $test,
        MutantExecutionResult $executionResult,
        string $expected,
    ): void {
        $teamCity = new TeamCity($timeoutsAsEscaped);

        $actual = $teamCity->testFinished($test, $executionResult);

        $this->assertSame($expected, $actual);
    }

    public static function executionResultProvider(): iterable
    {
        $nominalTest = new Test(
            id: 'mutantHash',
            name: 'Infection\Mutator\Boolean\LogicalAnd (mutantHash)',
            nodeId: '1A',
            parentNodeId: 'A',
        );

        $mutationDiff = <<<'PHP_DIFF'
            --- Original
            +++ Mutated
            @@ @@
            -$a = 10;
            +$a = 20;
            PHP_DIFF;
        $escapedMutationDiff = '--- Original|n+++ Mutated|n@@ @@|n-$a = 10;|n+$a = 20;';

        $nominalExecutionResultBuilder = MutantExecutionResultBuilder::withMinimalTestData()
            ->withMutatorClass(LogicalAndMutator::class)
            ->withMutatorName(MutatorName::getName(LogicalAndMutator::class))
            ->withMutantHash('mutantHash')
            ->withDetectionStatus(DetectionStatus::KILLED_BY_TESTS)
            ->withMutantDiff($mutationDiff)
            ->withProcessRuntime(3.);

        $expectedMessage = 'Mutator: LogicalAnd|nMutation ID: mutantHash|nMutation result: killed by tests';

        yield 'nominal' => [
            false,
            $nominalTest,
            $nominalExecutionResultBuilder
                ->build(),
            <<<TEAM_CITY
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (mutantHash)' nodeId='1A' message='{$expectedMessage}' details='{$escapedMutationDiff}' duration='3000']

                TEAM_CITY,
        ];

        yield 'timed-out with timeouts NOT counting as escaped' => [
            false,
            $nominalTest,
            $nominalExecutionResultBuilder
                ->withDetectionStatus(DetectionStatus::TIMED_OUT)
                ->build(),
            <<<TEAM_CITY
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (mutantHash)' nodeId='1A' message='Mutator: LogicalAnd|nMutation ID: mutantHash|nMutation result: timed out' details='{$escapedMutationDiff}' duration='3000']

                TEAM_CITY,
        ];

        yield 'timed-out with timeouts counting as escaped' => [
            true,
            $nominalTest,
            $nominalExecutionResultBuilder
                ->withDetectionStatus(DetectionStatus::TIMED_OUT)
                ->build(),
            <<<TEAM_CITY
                ##teamcity[testFailed name='Infection\Mutator\Boolean\LogicalAnd (mutantHash)' nodeId='1A' message='Mutator: LogicalAnd|nMutation ID: mutantHash|nMutation result: timed out' details='{$escapedMutationDiff}' duration='3000']

                TEAM_CITY,
        ];

        yield 'with an evaluation process that took some time' => [
            false,
            $nominalTest,
            $nominalExecutionResultBuilder
                ->withProcessRuntime(5.772644996643066)
                ->build(),
            <<<TEAM_CITY
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (mutantHash)' nodeId='1A' message='{$expectedMessage}' details='{$escapedMutationDiff}' duration='5773']

                TEAM_CITY,
        ];

        yield 'with an evaluation process that took some time (round half to upper)' => [
            false,
            $nominalTest,
            $nominalExecutionResultBuilder
                ->withProcessRuntime(5.7725)
                ->build(),
            <<<TEAM_CITY
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (mutantHash)' nodeId='1A' message='{$expectedMessage}' details='{$escapedMutationDiff}' duration='5773']

                TEAM_CITY,
        ];

        yield 'with an evaluation process that did not take any time (e.g. killed by an heuristic)' => [
            false,
            $nominalTest,
            $nominalExecutionResultBuilder
                ->withProcessRuntime(0.)
                ->build(),
            <<<TEAM_CITY
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (mutantHash)' nodeId='1A' message='{$expectedMessage}' details='{$escapedMutationDiff}' duration='0']

                TEAM_CITY,
        ];

        yield 'with an evaluation process that rounds down (fractional ms < 0.5)' => [
            false,
            $nominalTest,
            $nominalExecutionResultBuilder
                ->withProcessRuntime(5.7721)
                ->build(),
            <<<TEAM_CITY
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (mutantHash)' nodeId='1A' message='{$expectedMessage}' details='{$escapedMutationDiff}' duration='5772']

                TEAM_CITY,
        ];

        yield 'killed by static analysis' => [
            false,
            $nominalTest,
            $nominalExecutionResultBuilder
                ->withDetectionStatus(DetectionStatus::KILLED_BY_STATIC_ANALYSIS)
                ->withProcessRuntime(3.)
                ->build(),
            <<<TEAM_CITY
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (mutantHash)' nodeId='1A' message='Mutator: LogicalAnd|nMutation ID: mutantHash|nMutation result: killed by SA' details='{$escapedMutationDiff}' duration='3000']

                TEAM_CITY,
        ];

        yield 'error' => [
            false,
            $nominalTest,
            $nominalExecutionResultBuilder
                ->withDetectionStatus(DetectionStatus::ERROR)
                ->withProcessRuntime(3.)
                ->build(),
            <<<TEAM_CITY
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (mutantHash)' nodeId='1A' message='Mutator: LogicalAnd|nMutation ID: mutantHash|nMutation result: error' details='{$escapedMutationDiff}' duration='3000']

                TEAM_CITY,
        ];

        yield 'syntax error' => [
            false,
            $nominalTest,
            $nominalExecutionResultBuilder
                ->withDetectionStatus(DetectionStatus::SYNTAX_ERROR)
                ->withProcessRuntime(3.)
                ->build(),
            <<<TEAM_CITY
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (mutantHash)' nodeId='1A' message='Mutator: LogicalAnd|nMutation ID: mutantHash|nMutation result: syntax error' details='{$escapedMutationDiff}' duration='3000']

                TEAM_CITY,
        ];

        yield 'escaped' => [
            false,
            $nominalTest,
            $nominalExecutionResultBuilder
                ->withDetectionStatus(DetectionStatus::ESCAPED)
                ->withProcessRuntime(3.)
                ->build(),
            <<<TEAM_CITY
                ##teamcity[testFailed name='Infection\Mutator\Boolean\LogicalAnd (mutantHash)' nodeId='1A' message='Mutator: LogicalAnd|nMutation ID: mutantHash|nMutation result: escaped' details='{$escapedMutationDiff}' duration='3000']

                TEAM_CITY,
        ];

        yield 'skipped' => [
            false,
            $nominalTest,
            $nominalExecutionResultBuilder
                ->withDetectionStatus(DetectionStatus::SKIPPED)
                ->withProcessRuntime(3.)
                ->build(),
            <<<TEAM_CITY
                ##teamcity[testIgnored name='Infection\Mutator\Boolean\LogicalAnd (mutantHash)' nodeId='1A' message='Mutator: LogicalAnd|nMutation ID: mutantHash|nMutation result: skipped' details='{$escapedMutationDiff}' duration='3000']

                TEAM_CITY,
        ];

        yield 'not covered' => [
            false,
            $nominalTest,
            $nominalExecutionResultBuilder
                ->withDetectionStatus(DetectionStatus::NOT_COVERED)
                ->withProcessRuntime(3.)
                ->build(),
            <<<TEAM_CITY
                ##teamcity[testIgnored name='Infection\Mutator\Boolean\LogicalAnd (mutantHash)' nodeId='1A' message='Mutator: LogicalAnd|nMutation ID: mutantHash|nMutation result: not covered' details='{$escapedMutationDiff}' duration='3000']

                TEAM_CITY,
        ];

        yield 'ignored' => [
            false,
            $nominalTest,
            $nominalExecutionResultBuilder
                ->withDetectionStatus(DetectionStatus::IGNORED)
                ->withProcessRuntime(3.)
                ->build(),
            <<<TEAM_CITY
                ##teamcity[testIgnored name='Infection\Mutator\Boolean\LogicalAnd (mutantHash)' nodeId='1A' message='Mutator: LogicalAnd|nMutation ID: mutantHash|nMutation result: ignored' details='{$escapedMutationDiff}' duration='3000']

                TEAM_CITY,
        ];
    }
}
