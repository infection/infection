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

use function array_map;
use Closure;
use function implode;
use Infection\Logger\MutationAnalysis\MutationAnalysisLogger;
use Infection\Logger\MutationAnalysis\TeamCity\TeamCity;
use Infection\Logger\MutationAnalysis\TeamCity\TeamCityLogger;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutation\Mutation;
use Infection\Mutator\Boolean\LogicalAnd as LogicalAndMutator;
use Infection\Mutator\Boolean\LogicalOr as LogicalOrMutator;
use Infection\Mutator\Operator\Continue_;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use Infection\Tests\Mutation\MutationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;

#[CoversClass(TeamCity::class)]
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
        $cleanedUpExpected = RemoveInternalBlankLines::remove($expected);
        $execute($this->teamCityLogger);

        $actual = $this->getTeamCityLog();

        $this->assertSame($cleanedUpExpected, $actual);
    }

    public static function executionProvider(): iterable
    {
        yield 'one mutation executed for a source file' => [
            static function (MutationAnalysisLogger $logger): void {
                $sourceFilePath = '/path/to/project/src/Service/UserService.php';

                $logger->startAnalysis(1);

                $mutation = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath($sourceFilePath)
                    ->withMutatorClass(LogicalOrMutator::class)
                    ->build();
                $executionResult = self::createExecutionResult($mutation);

                $logger->startEvaluation($mutation);
                $logger->finishEvaluation($executionResult);
                $logger->finishMutationGenerationForFile(
                    $sourceFilePath,
                    [$mutation->getHash()],
                );

                $logger->finishAnalysis();
            },
            <<<'TEAM_CITY'
                ##teamcity[testSuiteStarted name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalOr (000fd378546ef75375056bea70335d8f)' flowId='5568c7d4af5ccc7f']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalOr (000fd378546ef75375056bea70335d8f)' flowId='5568c7d4af5ccc7f']

                ##teamcity[testSuiteFinished name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                TEAM_CITY,
        ];

        yield 'one mutation executed for a source file for which the mutations were all generated before all mutants were' => [
            static function (MutationAnalysisLogger $logger): void {
                $sourceFilePath = '/path/to/project/src/Service/UserService.php';

                $logger->startAnalysis(1);

                $mutation = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath($sourceFilePath)
                    ->withMutatorClass(LogicalOrMutator::class)
                    ->build();
                $executionResult = self::createExecutionResult($mutation);

                $logger->startEvaluation($mutation);

                $logger->finishMutationGenerationForFile(
                    $sourceFilePath,
                    [$mutation->getHash()],
                );

                $logger->finishEvaluation($executionResult);

                $logger->finishAnalysis();
            },
            <<<'TEAM_CITY'
                ##teamcity[testSuiteStarted name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalOr (000fd378546ef75375056bea70335d8f)' flowId='5568c7d4af5ccc7f']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalOr (000fd378546ef75375056bea70335d8f)' flowId='5568c7d4af5ccc7f']

                ##teamcity[testSuiteFinished name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                TEAM_CITY,
        ];

        yield 'two mutations executed for a source file, launched synchronously' => [
            static function (MutationAnalysisLogger $logger): void {
                $sourceFilePath = '/path/to/project/src/Service/UserService.php';

                $logger->startAnalysis(2);

                $mutation1 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath($sourceFilePath)
                    ->withMutatorClass(LogicalOrMutator::class)
                    ->build();
                $executionResult1 = self::createExecutionResult($mutation1);

                $mutation2 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath($sourceFilePath)
                    ->withMutatorClass(LogicalAndMutator::class)
                    ->build();
                $executionResult2 = self::createExecutionResult($mutation2);

                $logger->startEvaluation($mutation1);
                $logger->finishEvaluation($executionResult1);

                $logger->startEvaluation($mutation2);
                $logger->finishEvaluation($executionResult2);

                $logger->finishMutationGenerationForFile(
                    $sourceFilePath,
                    [
                        $mutation1->getHash(),
                        $mutation2->getHash(),
                    ],
                );

                $logger->finishAnalysis();
            },
            <<<'TEAM_CITY'
                ##teamcity[testSuiteStarted name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalOr (000fd378546ef75375056bea70335d8f)' flowId='5568c7d4af5ccc7f']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalOr (000fd378546ef75375056bea70335d8f)' flowId='5568c7d4af5ccc7f']

                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalAnd (000fd378546ef75375056bea70335d8f)' flowId='5568c7d4af5ccc7f']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (000fd378546ef75375056bea70335d8f)' flowId='5568c7d4af5ccc7f']

                ##teamcity[testSuiteFinished name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                TEAM_CITY,
        ];

        yield 'three mutations executed for a source file, launched in parallel' => [
            static function (MutationAnalysisLogger $logger): void {
                $logger->startAnalysis(2);

                $sourceFilePath = '/path/to/project/src/Service/UserService.php';

                $mutation1 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath($sourceFilePath)
                    ->withMutatorClass(LogicalOrMutator::class)
                    ->withMutatorName('LogicalOr')
                    ->build();
                $executionResult1 = self::createExecutionResult($mutation1);

                $mutation2 = MutationBuilder::from($mutation1)
                    ->withMutatorClass(LogicalAndMutator::class)
                    ->withMutatorName('LogicalAnd')
                    ->build();
                $executionResult2 = self::createExecutionResult($mutation2);

                $mutation3 = MutationBuilder::from($mutation1)
                    ->withMutatorClass(Continue_::class)
                    ->withMutatorName('Continue_')
                    ->build();
                $executionResult3 = self::createExecutionResult($mutation3);

                // Sanity check
                self::assertNotSame($mutation1->getHash(), $mutation2->getHash());

                $logger->startEvaluation($mutation1);
                $logger->startEvaluation($mutation2);
                $logger->startEvaluation($mutation3);

                $logger->finishEvaluation($executionResult2);
                $logger->finishEvaluation($executionResult3);
                $logger->finishEvaluation($executionResult1);

                $logger->finishMutationGenerationForFile(
                    $sourceFilePath,
                    [
                        $mutation1->getHash(),
                        $mutation2->getHash(),
                        $mutation3->getHash(),
                    ],
                );

                $logger->finishAnalysis();
            },
            <<<'TEAM_CITY'
                ##teamcity[testSuiteStarted name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalOr (49a5dfcd2f4a0b33d4a02e662812af55)' flowId='5568c7d4af5ccc7f']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalOr (49a5dfcd2f4a0b33d4a02e662812af55)' flowId='5568c7d4af5ccc7f']

                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalAnd (aa35bf87f287aa4e383112a632fde848)' flowId='5568c7d4af5ccc7f']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (aa35bf87f287aa4e383112a632fde848)' flowId='5568c7d4af5ccc7f']

                ##teamcity[testStarted name='Infection\Mutator\Operator\Continue_ (9272ac9a2aff44767733cf23a4acb7c6)' flowId='5568c7d4af5ccc7f']
                ##teamcity[testFinished name='Infection\Mutator\Operator\Continue_ (9272ac9a2aff44767733cf23a4acb7c6)' flowId='5568c7d4af5ccc7f']

                ##teamcity[testSuiteFinished name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                TEAM_CITY,
        ];

        yield 'two mutations executed for a source file, launched in parallel with the mutation generation finishing before all mutants are evaluated' => [
            static function (MutationAnalysisLogger $logger): void {
                $logger->startAnalysis(2);

                $sourceFilePath = '/path/to/project/src/Service/UserService.php';

                $mutation1 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath($sourceFilePath)
                    ->withMutatorClass(LogicalOrMutator::class)
                    ->withMutatorName('LogicalOr')
                    ->build();
                $executionResult1 = self::createExecutionResult($mutation1);

                $mutation2 = MutationBuilder::from($mutation1)
                    ->withMutatorClass(LogicalAndMutator::class)
                    ->withMutatorName('LogicalAnd')
                    ->build();
                $executionResult2 = self::createExecutionResult($mutation2);

                // Sanity check
                self::assertNotSame($mutation1->getHash(), $mutation2->getHash());

                $logger->startEvaluation($mutation1);
                $logger->startEvaluation($mutation2);

                $logger->finishEvaluation($executionResult2);

                $logger->finishMutationGenerationForFile(
                    $sourceFilePath,
                    [
                        $mutation1->getHash(),
                        $mutation2->getHash(),
                    ],
                );

                $logger->finishEvaluation($executionResult1);

                $logger->finishAnalysis();
            },
            <<<'TEAM_CITY'
                ##teamcity[testSuiteStarted name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalOr (49a5dfcd2f4a0b33d4a02e662812af55)' flowId='5568c7d4af5ccc7f']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalOr (49a5dfcd2f4a0b33d4a02e662812af55)' flowId='5568c7d4af5ccc7f']

                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalAnd (aa35bf87f287aa4e383112a632fde848)' flowId='5568c7d4af5ccc7f']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (aa35bf87f287aa4e383112a632fde848)' flowId='5568c7d4af5ccc7f']

                ##teamcity[testSuiteFinished name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                TEAM_CITY,
        ];

        yield 'one mutation executed for two source file, launched synchronously' => [
            static function (MutationAnalysisLogger $logger): void {
                $logger->startAnalysis(2);

                $sourceFilePath1 = '/path/to/project/src/Service/UserService.php';
                $sourceFilePath2 = '/path/to/project/src/Service/ContactService.php';

                $mutation1 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath($sourceFilePath1)
                    ->withMutatorClass(LogicalOrMutator::class)
                    ->withMutatorName('LogicalOr')
                    ->build();
                $executionResult1 = self::createExecutionResult($mutation1);

                $mutation2 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath($sourceFilePath2)
                    ->withMutatorClass(LogicalAndMutator::class)
                    ->withMutatorName('LogicalAnd')
                    ->build();
                $executionResult2 = self::createExecutionResult($mutation2);

                // Sanity check
                self::assertNotSame($mutation1->getHash(), $mutation2->getHash());

                $logger->startEvaluation($mutation1);
                $logger->finishEvaluation($executionResult1);
                $logger->finishMutationGenerationForFile(
                    $sourceFilePath1,
                    [$mutation1->getHash()],
                );

                $logger->startEvaluation($mutation2);
                $logger->finishEvaluation($executionResult2);
                $logger->finishMutationGenerationForFile(
                    $sourceFilePath2,
                    [$mutation2->getHash()],
                );

                $logger->finishAnalysis();
            },
            <<<'TEAM_CITY'
                ##teamcity[testSuiteStarted name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalOr (49a5dfcd2f4a0b33d4a02e662812af55)' flowId='5568c7d4af5ccc7f']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalOr (49a5dfcd2f4a0b33d4a02e662812af55)' flowId='5568c7d4af5ccc7f']

                ##teamcity[testSuiteFinished name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                ##teamcity[testSuiteStarted name='src/Service/ContactService.php' flowId='12f6def551a5aae7']

                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalAnd (0a451675763250c03e95b626f7bcfb7d)' flowId='12f6def551a5aae7']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (0a451675763250c03e95b626f7bcfb7d)' flowId='12f6def551a5aae7']

                ##teamcity[testSuiteFinished name='src/Service/ContactService.php' flowId='12f6def551a5aae7']

                TEAM_CITY,
        ];

        yield 'one mutation executed for two source file, launched in parallel' => [
            static function (MutationAnalysisLogger $logger): void {
                $logger->startAnalysis(2);

                $sourceFilePath1 = '/path/to/project/src/Service/UserService.php';
                $sourceFilePath2 = '/path/to/project/src/Service/ContactService.php';

                $mutation1 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath($sourceFilePath1)
                    ->withMutatorClass(LogicalOrMutator::class)
                    ->withMutatorName('LogicalOr')
                    ->build();
                $executionResult1 = self::createExecutionResult($mutation1);

                $mutation2 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath($sourceFilePath2)
                    ->withMutatorClass(LogicalAndMutator::class)
                    ->withMutatorName('LogicalAnd')
                    ->build();
                $executionResult2 = self::createExecutionResult($mutation2);

                // Sanity check
                self::assertNotSame($mutation1->getHash(), $mutation2->getHash());

                $logger->startEvaluation($mutation1);
                $logger->startEvaluation($mutation2);

                $logger->finishEvaluation($executionResult2);
                $logger->finishMutationGenerationForFile(
                    $sourceFilePath1,
                    [$mutation1->getHash()],
                );

                $logger->finishEvaluation($executionResult1);
                $logger->finishMutationGenerationForFile(
                    $sourceFilePath2,
                    [$mutation2->getHash()],
                );

                $logger->finishAnalysis();
            },
            <<<'TEAM_CITY'
                ##teamcity[testSuiteStarted name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalOr (49a5dfcd2f4a0b33d4a02e662812af55)' flowId='5568c7d4af5ccc7f']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalOr (49a5dfcd2f4a0b33d4a02e662812af55)' flowId='5568c7d4af5ccc7f']

                ##teamcity[testSuiteFinished name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                ##teamcity[testSuiteStarted name='src/Service/ContactService.php' flowId='12f6def551a5aae7']

                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalAnd (0a451675763250c03e95b626f7bcfb7d)' flowId='12f6def551a5aae7']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (0a451675763250c03e95b626f7bcfb7d)' flowId='12f6def551a5aae7']

                ##teamcity[testSuiteFinished name='src/Service/ContactService.php' flowId='12f6def551a5aae7']

                TEAM_CITY,
        ];

        yield 'mutations executed for multiple source file, launched in parallel (taken from a real execution)' => [
            static function (MutationAnalysisLogger $logger): void {
                $logger->startAnalysis(2);

                $sourceFilePath1 = '/path/to/project/src/Service/UserService.php';
                $sourceFilePath2 = '/path/to/project/src/Service/ContactService.php';

                $mutation1A = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath($sourceFilePath1)
                    ->withMutatorClass(LogicalOrMutator::class)
                    ->withMutatorName('LogicalOr')
                    ->build();
                $executionResult1A = self::createExecutionResult($mutation1A);

                $mutation1B = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath($sourceFilePath1)
                    ->withMutatorClass(LogicalAndMutator::class)
                    ->withMutatorName('LogicalAnd')
                    ->build();
                $executionResult1B = self::createExecutionResult($mutation1B);

                $mutation2 = MutationBuilder::withMinimalTestData()
                    ->withOriginalFilePath($sourceFilePath2)
                    ->withMutatorClass(LogicalAndMutator::class)
                    ->withMutatorName('BooleanNot')
                    ->build();
                $executionResult2 = self::createExecutionResult($mutation2);

                // Sanity check
                self::assertNotSame($mutation1A->getHash(), $mutation1B->getHash());

                $logger->startEvaluation($mutation1A);
                $logger->startEvaluation($mutation1B);

                $logger->finishMutationGenerationForFile(
                    $sourceFilePath1,
                    [$mutation1A->getHash(), $mutation1B->getHash()],
                );

                $logger->startEvaluation($mutation2);

                $logger->finishEvaluation($executionResult1A);
                $logger->finishEvaluation($executionResult1B);

                $logger->finishMutationGenerationForFile(
                    $sourceFilePath2,
                    [$mutation2->getHash()],
                );

                $logger->finishEvaluation($executionResult2);

                $logger->finishAnalysis();
            },
            <<<'TEAM_CITY'
                ##teamcity[testSuiteStarted name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalOr (49a5dfcd2f4a0b33d4a02e662812af55)' flowId='5568c7d4af5ccc7f']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalOr (49a5dfcd2f4a0b33d4a02e662812af55)' flowId='5568c7d4af5ccc7f']

                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalAnd (aa35bf87f287aa4e383112a632fde848)' flowId='5568c7d4af5ccc7f']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (aa35bf87f287aa4e383112a632fde848)' flowId='5568c7d4af5ccc7f']

                ##teamcity[testSuiteFinished name='src/Service/UserService.php' flowId='5568c7d4af5ccc7f']

                ##teamcity[testSuiteStarted name='src/Service/ContactService.php' flowId='12f6def551a5aae7']

                ##teamcity[testStarted name='Infection\Mutator\Boolean\LogicalAnd (a382bb75e854bae469f9da5ff3ac6a7b)' flowId='12f6def551a5aae7']
                ##teamcity[testFinished name='Infection\Mutator\Boolean\LogicalAnd (a382bb75e854bae469f9da5ff3ac6a7b)' flowId='12f6def551a5aae7']

                ##teamcity[testSuiteFinished name='src/Service/ContactService.php' flowId='12f6def551a5aae7']

                TEAM_CITY,
        ];
    }

    private static function createExecutionResult(Mutation $mutation): MutantExecutionResult
    {
        return MutantExecutionResultBuilder::withMinimalTestData()
            ->withOriginalFilePath($mutation->getOriginalFilePath())
            ->withMutatorClass($mutation->getMutatorClass())
            ->withMutantHash($mutation->getHash())
            ->build();
    }

    private function getTeamCityLog(): string
    {
        return implode(
            '',
            array_map(
                static fn (array $record): string => (string) $record['message'],
                $this->testLogger->recordsByLevel[LogLevel::WARNING],
            ),
        );
    }
}
