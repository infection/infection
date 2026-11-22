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

namespace Infection\Tests\TestFramework;

use function array_map;
use Infection\Mutant\DetectionStatus;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\CombinedTestFramework;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

#[CoversClass(CombinedTestFramework::class)]
final class CombinedTestFrameworkTest extends TestCase
{
    public function test_it_preserves_the_first_evaluation_without_a_follow_up_framework(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();

        $evaluation = $this->createStub(MutantProcessContainer::class);

        $firstTestFramework = $this->createMock(TestFramework::class);
        $firstTestFramework
            ->expects($this->once())
            ->method('test')
            ->with($this->identicalTo($mutant))
            ->willReturn($evaluation)
        ;

        $framework = new CombinedTestFramework($firstTestFramework);

        $this->assertSame($evaluation, $framework->test($mutant));
    }

    #[DataProvider('evaluationStatusProvider')]
    public function test_it_evaluates_frameworks_in_order_only_after_preceding_evaluations_escape(
        DetectionStatus $firstStatus,
        DetectionStatus $followUpStatus,
        bool $hasNext,
        bool $hasLast,
    ): void {
        $mutant = MutantBuilder::withMinimalTestData()->build();

        $result = MutantExecutionResultBuilder::withMinimalTestData()
            ->withDetectionStatus($firstStatus)
            ->build()
        ;

        $followUpTestFramework = $this->createMock(TestFramework::class);
        $followUpProcess = $this->createStub(MutantProcess::class);
        $followUpProcess
            ->method('getMutantExecutionResult')
            ->willReturn(
                MutantExecutionResultBuilder::withMinimalTestData()
                    ->withDetectionStatus(DetectionStatus::ESCAPED)
                    ->build(),
            )
        ;
        $lastProcess = $this->createStub(MutantProcess::class);
        $lastProcess
            ->method('getMutantExecutionResult')
            ->willReturn(
                MutantExecutionResultBuilder::withMinimalTestData()
                    ->withDetectionStatus($followUpStatus)
                    ->build(),
            )
        ;
        $finalProcess = $this->createStub(MutantProcess::class);
        $lastTestFramework = $this->createMock(TestFramework::class);

        $firstProcess = $this->createStub(MutantProcess::class);
        $firstProcess
            ->method('getMutantExecutionResult')
            ->willReturn($result)
        ;
        $firstEvaluation = MutantProcessContainer::from(static fn (): MutantProcess => $firstProcess);
        $firstTestFramework = $this->createMock(TestFramework::class);
        $firstTestFramework
            ->expects($this->once())
            ->method('test')
            ->with($this->identicalTo($mutant))
            ->willReturn($firstEvaluation)
        ;

        $state = new stdClass();
        $state->firstEvaluationFinished = false;
        $followUpTestFramework
            ->expects(
                $hasNext
                    ? $this->once()
                    : $this->never(),
            )
            ->method('test')
            ->with($this->identicalTo($mutant))
            ->willReturnCallback(function () use ($state, $followUpProcess, $lastProcess): MutantProcessContainer {
                $this->assertTrue($state->firstEvaluationFinished);

                return MutantProcessContainer::from(static fn (): MutantProcess => $followUpProcess)
                    ->append(static fn (): MutantProcess => $lastProcess)
                ;
            })
        ;

        $state->followUpEvaluationFinished = false;
        $lastTestFramework
            ->expects(
                $hasLast
                    ? $this->once()
                    : $this->never(),
            )
            ->method('test')
            ->with($this->identicalTo($mutant))
            ->willReturnCallback(function () use ($state, $finalProcess): MutantProcessContainer {
                $this->assertTrue($state->followUpEvaluationFinished);

                return MutantProcessContainer::from(static fn (): MutantProcess => $finalProcess);
            })
        ;

        $framework = new CombinedTestFramework($firstTestFramework, $followUpTestFramework, $lastTestFramework);
        $evaluation = $framework->test($mutant);

        $this->assertSame($firstEvaluation, $evaluation);
        $this->assertSame($firstProcess, $evaluation->getCurrent());
        $this->assertSame($hasNext, $evaluation->hasNext());
        $state->firstEvaluationFinished = true;

        if ($hasNext) {
            $this->assertSame($followUpProcess, $evaluation->createNext());
            $this->assertSame($followUpProcess, $evaluation->getCurrent());
            $this->assertTrue($evaluation->hasNext());
            $this->assertSame($lastProcess, $evaluation->createNext());
            $this->assertSame($lastProcess, $evaluation->getCurrent());
            $state->followUpEvaluationFinished = true;

            if (!$hasLast) {
                $this->assertFalse($evaluation->hasNext());

                return;
            }

            $this->assertTrue($evaluation->hasNext());
            $this->assertSame($finalProcess, $evaluation->createNext());
            $this->assertSame($finalProcess, $evaluation->getCurrent());
            $this->assertFalse($evaluation->hasNext());
        }
    }

    /**
     * @param non-empty-list<array{string, string}> $identities
     */
    #[DataProvider('frameworkIdentityProvider')]
    public function test_it_combines_framework_names_and_versions(
        array $identities,
        string $expectedName,
        string $expectedVersion,
    ): void {
        $frameworks = array_map(
            function (array $identity): TestFramework {
                $framework = $this->createStub(TestFramework::class);
                $framework->method('getName')->willReturn($identity[0]);
                $framework->method('getVersion')->willReturn($identity[1]);

                return $framework;
            },
            $identities,
        );
        $framework = new CombinedTestFramework(...$frameworks);

        $this->assertSame($expectedName, $framework->getName());
        $this->assertSame($expectedVersion, $framework->getVersion());
    }

    /**
     * @param 'checkRequirements'|'executeInitialRun' $operation
     */
    #[DataProvider('frameworkOperationProvider')]
    public function test_it_runs_framework_operations_in_order_and_stops_on_failure(
        string $operation,
        ?int $failureIndex,
    ): void {
        $calls = [];
        $failure = new RuntimeException('Framework operation failed.');
        $frameworks = [];

        foreach ([0, 1, 2] as $index) {
            $framework = $this->createMock(TestFramework::class);
            $framework
                ->expects(
                    $failureIndex === null || $index <= $failureIndex
                        ? $this->once()
                        : $this->never(),
                )
                ->method($operation)
                ->willReturnCallback(function () use (&$calls, $index, $operation, $failureIndex, $failure): ?InitialRunResults {
                    $this->assertCount($index, $calls);
                    $calls[] = $index;

                    if ($index === $failureIndex) {
                        throw $failure;
                    }

                    return $operation === 'executeInitialRun'
                        ? new InitialRunResults('Individual framework output', 24.0)
                        : null;
                })
            ;
            $frameworks[] = $framework;
        }

        $framework = new CombinedTestFramework(...$frameworks);

        if ($failureIndex !== null) {
            $this->expectExceptionObject($failure);
        }

        if ($operation === 'checkRequirements') {
            $framework->checkRequirements();

            $this->assertSame([0, 1, 2], $calls);

            return;
        }

        $result = $framework->executeInitialRun();

        $this->assertSame([0, 1, 2], $calls);
        $this->assertEquals(new InitialRunResults('', null), $result);
    }

    /**
     * @param non-empty-list<bool> $reports
     */
    #[DataProvider('junitReportProvider')]
    public function test_it_has_a_junit_report_when_any_framework_provides_one(array $reports, bool $expected): void
    {
        $frameworks = array_map(
            function (bool $hasReport): TestFramework {
                $framework = $this->createStub(TestFramework::class);
                $framework->method('hasJUnitReport')->willReturn($hasReport);

                return $framework;
            },
            $reports,
        );
        $framework = new CombinedTestFramework(...$frameworks);

        // @phpstan-ignore method.deprecated (Exercise the legacy capability while it remains in the contract.)
        $this->assertSame($expected, $framework->hasJUnitReport());
    }

    public static function frameworkIdentityProvider(): iterable
    {
        yield 'one framework' => [[['PHPUnit', '12.5']], 'PHPUnit', '12.5'];

        yield 'three frameworks' => [
            [['PHPUnit', '12.5'], ['PHPStan', '2.1'], ['Mago', '1.59']],
            'PHPUnit, PHPStan, Mago',
            '12.5, 2.1, 1.59',
        ];

        yield 'repeated names and unknown version' => [
            [['PHPUnit', '12.5'], ['PHPUnit', '']],
            'PHPUnit, PHPUnit',
            '12.5, ',
        ];
    }

    public static function frameworkOperationProvider(): iterable
    {
        yield 'checks all requirements' => ['checkRequirements', null];

        yield 'executes all initial runs' => ['executeInitialRun', null];

        yield 'a failed requirement stops later checks' => ['checkRequirements', 1];

        yield 'a failed initial run stops later runs' => ['executeInitialRun', 1];
    }

    public static function junitReportProvider(): iterable
    {
        yield 'single framework with a report' => [[true], true];

        yield 'no reports' => [[false, false, false], false];

        yield 'first framework provides a report' => [[true, false, false], true];

        yield 'middle framework provides a report' => [[false, true, false], true];

        yield 'last framework provides a report' => [[false, false, true], true];
    }

    public static function evaluationStatusProvider(): iterable
    {
        yield 'all frameworks escape' => [DetectionStatus::ESCAPED, DetectionStatus::ESCAPED, true, true];

        yield 'first killed by tests' => [DetectionStatus::KILLED_BY_TESTS, DetectionStatus::ESCAPED, false, false];

        yield 'first errors' => [DetectionStatus::ERROR, DetectionStatus::ESCAPED, false, false];

        yield 'first times out' => [DetectionStatus::TIMED_OUT, DetectionStatus::ESCAPED, false, false];

        yield 'first syntax error' => [DetectionStatus::SYNTAX_ERROR, DetectionStatus::ESCAPED, false, false];

        yield 'follow-up killed by static analysis' => [DetectionStatus::ESCAPED, DetectionStatus::KILLED_BY_STATIC_ANALYSIS, true, false];

        yield 'follow-up errors' => [DetectionStatus::ESCAPED, DetectionStatus::ERROR, true, false];

        yield 'follow-up times out' => [DetectionStatus::ESCAPED, DetectionStatus::TIMED_OUT, true, false];

        yield 'follow-up syntax error' => [DetectionStatus::ESCAPED, DetectionStatus::SYNTAX_ERROR, true, false];
    }
}
