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

use function array_filter;
use function array_map;
use Closure;
use function explode;
use function implode;
use Infection\Logger\MutationAnalysis\MutationAnalysisLogger;
use Infection\Logger\MutationAnalysis\TeamCity\TeamCity;
use Infection\Logger\MutationAnalysis\TeamCity\TeamCityLogger;
use Infection\Mutator\Boolean\LogicalAnd as LogicalAndMutator;
use Infection\Mutator\Boolean\LogicalOr as LogicalOrMutator;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use Infection\Tests\Mutation\MutationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;

#[CoversClass(TeamCityLogger::class)]
final class TeamCityLoggerTest extends TestCase
{
    private TestLogger $testLogger;

    private MutationAnalysisLogger $teamCityLogger;

    protected function setUp(): void
    {
        $this->testLogger = new TestLogger();

        $this->teamCityLogger = new TeamCityLogger(
            new TeamCity(),
            $this->testLogger,
            '/path/to/project',
        );
    }

    /**
     * @param Closure(MutationAnalysisLogger):void $execute
     */
    #[DataProvider('executionProvider')]
    public function test_it_can_log_an_execution(
        Closure $execute,
        string $expected,
    ): void {
        // We allow blank lines in expected for readability.
        $cleanedUpExpected = self::removeBlankLines($expected);
        $execute($this->teamCityLogger);

        $actual = $this->getTeamCityLog();

        $this->assertSame($cleanedUpExpected, $actual);
    }

    public static function executionProvider(): iterable
    {
        yield 'one mutation executed for a source file' => [
            static function (MutationAnalysisLogger $logger): void {
                $mutationCount = 1;
                $logger->startAnalysis($mutationCount);

                $mutation = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath('/path/to/project/src/Service/UserService.php')
                    ->withMutatorClass(LogicalOrMutator::class)
                    ->build();
                $executionResult = MutantExecutionResultBuilder::withMinimalTestData()
                    ->withMutatorClass($mutation->getMutatorClass())
                    ->withMutantHash($mutation->getHash())
                    ->build();

                $logger->startEvaluation($mutation, $mutationCount);
                $logger->finishEvaluation($executionResult, $mutationCount);

                $logger->finishAnalysis();
            },
            <<<'TEAM_CITY'
                ##teamcity[flowStarted flowId='5568c7d4af5ccc7f']
                ##teamcity[testSuiteStarted name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']
            
                ##teamcity[flowStarted flowId='dafea228a2b182fe' parent='5568c7d4af5ccc7f']
                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalOr' flowId='dafea228a2b182fe']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalOr' flowId='dafea228a2b182fe']
                ##teamcity[flowFinished flowId='dafea228a2b182fe']
            
                ##teamcity[testSuiteFinished name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']
                ##teamcity[flowFinished flowId='5568c7d4af5ccc7f']
                TEAM_CITY,
        ];

        yield 'two mutations executed for a source file, launched synchronously' => [
            static function (MutationAnalysisLogger $logger): void {
                $mutationCount = 2;
                $logger->startAnalysis($mutationCount);

                $mutation1 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath('/path/to/project/src/Service/UserService.php')
                    ->withMutatorClass(LogicalOrMutator::class)
                    ->build();
                $executionResult1 = MutantExecutionResultBuilder::withMinimalTestData()
                    ->withMutatorClass($mutation1->getMutatorClass())
                    ->withMutantHash($mutation1->getHash())
                    ->build();

                $mutation2 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath('/path/to/project/src/Service/UserService.php')
                    ->withMutatorClass(LogicalAndMutator::class)
                    ->build();
                $executionResult2 = MutantExecutionResultBuilder::withMinimalTestData()
                    ->withMutatorClass($mutation2->getMutatorClass())
                    ->withMutantHash($mutation2->getHash())
                    ->build();

                $logger->startEvaluation($mutation1, $mutationCount);
                $logger->finishEvaluation($executionResult1, $mutationCount);

                $logger->startEvaluation($mutation2, $mutationCount);
                $logger->finishEvaluation($executionResult2, $mutationCount);

                $logger->finishAnalysis();
            },
            <<<'TEAM_CITY'
                ##teamcity[flowStarted flowId='5568c7d4af5ccc7f']
                ##teamcity[testSuiteStarted name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']
            
                ##teamcity[flowStarted flowId='dafea228a2b182fe' parent='5568c7d4af5ccc7f']
                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalOr' flowId='dafea228a2b182fe']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalOr' flowId='dafea228a2b182fe']
                ##teamcity[flowFinished flowId='dafea228a2b182fe']
            
                ##teamcity[flowStarted flowId='dafea228a2b182fe' parent='5568c7d4af5ccc7f']
                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalAnd' flowId='dafea228a2b182fe']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd' flowId='dafea228a2b182fe']
                ##teamcity[flowFinished flowId='dafea228a2b182fe']
            
                ##teamcity[testSuiteFinished name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']
                ##teamcity[flowFinished flowId='5568c7d4af5ccc7f']
                TEAM_CITY,
        ];

        yield 'two mutations executed for a source file, launched in parallel' => [
            static function (MutationAnalysisLogger $logger): void {
                $mutationCount = 2;
                $logger->startAnalysis($mutationCount);

                $mutation1 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath('/path/to/project/src/Service/UserService.php')
                    ->withMutatorClass(LogicalOrMutator::class)
                    ->withMutatorName('LogicalOr')
                    ->build();
                $executionResult1 = MutantExecutionResultBuilder::withMinimalTestData()
                    ->withMutatorClass($mutation1->getMutatorClass())
                    ->withMutantHash($mutation1->getHash())
                    ->build();

                $mutation2 = MutationBuilder::from($mutation1)
                    ->withMutatorClass(LogicalAndMutator::class)
                    ->withMutatorName('LogicalAnd')
                    ->build();
                $executionResult2 = MutantExecutionResultBuilder::withMinimalTestData()
                    ->withMutatorClass($mutation2->getMutatorClass())
                    ->withMutantHash($mutation2->getHash())
                    ->build();

                // Sanity check
                self::assertNotSame($mutation1->getHash(), $mutation2->getHash());

                $logger->startEvaluation($mutation1, $mutationCount);
                $logger->startEvaluation($mutation2, $mutationCount);

                $logger->finishEvaluation($executionResult2, $mutationCount);
                $logger->finishEvaluation($executionResult1, $mutationCount);

                $logger->finishAnalysis();
            },
            <<<'TEAM_CITY'
                ##teamcity[flowStarted flowId='5568c7d4af5ccc7f']
                ##teamcity[testSuiteStarted name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']
            
                ##teamcity[flowStarted flowId='18830ccd5b35e676' parent='5568c7d4af5ccc7f']
                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalOr' flowId='18830ccd5b35e676']
            
                ##teamcity[flowStarted flowId='a20ac7aa8518e530' parent='5568c7d4af5ccc7f']
                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalAnd' flowId='a20ac7aa8518e530']
            
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd' flowId='a20ac7aa8518e530']
                ##teamcity[flowFinished flowId='a20ac7aa8518e530']
            
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalOr' flowId='18830ccd5b35e676']
                ##teamcity[flowFinished flowId='18830ccd5b35e676']
            
                ##teamcity[testSuiteFinished name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']
                ##teamcity[flowFinished flowId='5568c7d4af5ccc7f']
                TEAM_CITY,
        ];

        yield 'one mutation executed for two source file, launched synchronously' => [
            static function (MutationAnalysisLogger $logger): void {
                $mutationCount = 2;
                $logger->startAnalysis($mutationCount);

                $mutation1 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath('/path/to/project/src/Service/UserService.php')
                    ->withMutatorClass(LogicalOrMutator::class)
                    ->withMutatorName('LogicalOr')
                    ->build();
                $executionResult1 = MutantExecutionResultBuilder::withMinimalTestData()
                    ->withMutatorClass($mutation1->getMutatorClass())
                    ->withMutantHash($mutation1->getHash())
                    ->build();

                $mutation2 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath('/path/to/project/src/Service/ContactService.php')
                    ->withMutatorClass(LogicalAndMutator::class)
                    ->withMutatorName('LogicalAnd')
                    ->build();
                $executionResult2 = MutantExecutionResultBuilder::withMinimalTestData()
                    ->withMutatorClass($mutation2->getMutatorClass())
                    ->withMutantHash($mutation2->getHash())
                    ->build();

                // Sanity check
                self::assertNotSame($mutation1->getHash(), $mutation2->getHash());

                $logger->startEvaluation($mutation1, $mutationCount);
                $logger->finishEvaluation($executionResult1, $mutationCount);

                $logger->startEvaluation($mutation2, $mutationCount);
                $logger->finishEvaluation($executionResult2, $mutationCount);

                $logger->finishAnalysis();
            },
            <<<'TEAM_CITY'
                ##teamcity[flowStarted flowId='5568c7d4af5ccc7f']
                ##teamcity[testSuiteStarted name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']
            
                ##teamcity[flowStarted flowId='18830ccd5b35e676' parent='5568c7d4af5ccc7f']
                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalOr' flowId='18830ccd5b35e676']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalOr' flowId='18830ccd5b35e676']
                ##teamcity[flowFinished flowId='18830ccd5b35e676']
            
                ##teamcity[flowStarted flowId='12f6def551a5aae7']
                ##teamcity[testSuiteStarted name='src/Service/ContactService.php' flowId='12f6def551a5aae7']
            
                ##teamcity[flowStarted flowId='ea74aba5c3e84a26' parent='12f6def551a5aae7']
                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalAnd' flowId='ea74aba5c3e84a26']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd' flowId='ea74aba5c3e84a26']
                ##teamcity[flowFinished flowId='ea74aba5c3e84a26']
            
                ##teamcity[testSuiteFinished name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']
                ##teamcity[flowFinished flowId='5568c7d4af5ccc7f']
            
                ##teamcity[testSuiteFinished name='src/Service/ContactService.php' flowId='12f6def551a5aae7']
                ##teamcity[flowFinished flowId='12f6def551a5aae7']
                TEAM_CITY,
        ];

        yield 'one mutation executed for two source file, launched in parallel' => [
            static function (MutationAnalysisLogger $logger): void {
                $mutationCount = 2;
                $logger->startAnalysis($mutationCount);

                $mutation1 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath('/path/to/project/src/Service/UserService.php')
                    ->withMutatorClass(LogicalOrMutator::class)
                    ->withMutatorName('LogicalOr')
                    ->build();
                $executionResult1 = MutantExecutionResultBuilder::withMinimalTestData()
                    ->withMutatorClass($mutation1->getMutatorClass())
                    ->withMutantHash($mutation1->getHash())
                    ->build();

                $mutation2 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath('/path/to/project/src/Service/ContactService.php')
                    ->withMutatorClass(LogicalAndMutator::class)
                    ->withMutatorName('LogicalAnd')
                    ->build();
                $executionResult2 = MutantExecutionResultBuilder::withMinimalTestData()
                    ->withMutatorClass($mutation2->getMutatorClass())
                    ->withMutantHash($mutation2->getHash())
                    ->build();

                // Sanity check
                self::assertNotSame($mutation1->getHash(), $mutation2->getHash());

                $logger->startEvaluation($mutation1, $mutationCount);
                $logger->startEvaluation($mutation2, $mutationCount);

                $logger->finishEvaluation($executionResult2, $mutationCount);
                $logger->finishEvaluation($executionResult1, $mutationCount);

                $logger->finishAnalysis();
            },
            <<<'TEAM_CITY'
                ##teamcity[flowStarted flowId='5568c7d4af5ccc7f']
                ##teamcity[testSuiteStarted name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']
            
                ##teamcity[flowStarted flowId='18830ccd5b35e676' parent='5568c7d4af5ccc7f']
                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalOr' flowId='18830ccd5b35e676']
            
                ##teamcity[flowStarted flowId='12f6def551a5aae7']
                ##teamcity[testSuiteStarted name='src/Service/ContactService.php' flowId='12f6def551a5aae7']
            
                ##teamcity[flowStarted flowId='ea74aba5c3e84a26' parent='12f6def551a5aae7']
                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalAnd' flowId='ea74aba5c3e84a26']
            
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd' flowId='ea74aba5c3e84a26']
                ##teamcity[flowFinished flowId='ea74aba5c3e84a26']
            
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalOr' flowId='18830ccd5b35e676']
                ##teamcity[flowFinished flowId='18830ccd5b35e676']
            
                ##teamcity[testSuiteFinished name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']
                ##teamcity[flowFinished flowId='5568c7d4af5ccc7f']
            
                ##teamcity[testSuiteFinished name='src/Service/ContactService.php' flowId='12f6def551a5aae7']
                ##teamcity[flowFinished flowId='12f6def551a5aae7']
                TEAM_CITY,
        ];
    }

    private static function removeBlankLines(string $lines): string
    {
        return implode(
            "\n",
            array_filter(
                explode("\n", $lines),
            ),
        );
    }

    private function getTeamCityLog(): string
    {
        return implode(
            '',
            array_map(
                static fn (array $record): string => $record['message'],
                $this->testLogger->recordsByLevel[LogLevel::WARNING],
            ),
        );
    }
}
