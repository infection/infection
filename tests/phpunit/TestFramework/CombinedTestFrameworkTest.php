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

use BadMethodCallException;
use Closure;
use Infection\Mutant\DetectionStatus;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\CombinedTestFramework;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CombinedTestFramework::class)]
final class CombinedTestFrameworkTest extends TestCase
{
    public function test_it_preserves_the_test_framework_evaluation_when_static_analysis_is_disabled(): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $evaluation = $this->createStub(MutantProcessContainer::class);
        $testFramework = $this->createMock(TestFramework::class);
        $testFramework
            ->expects($this->once())
            ->method('test')
            ->with($this->identicalTo($mutant))
            ->willReturn($evaluation)
        ;

        $framework = new CombinedTestFramework($testFramework, null);

        $this->assertSame($evaluation, $framework->test($mutant));
    }

    #[DataProvider('evaluationStatusProvider')]
    public function test_it_only_creates_static_analysis_processes_after_the_tests_escape(DetectionStatus $status, bool $hasNext): void
    {
        $mutant = MutantBuilder::withMinimalTestData()->build();
        $testProcess = $this->createStub(MutantProcess::class);
        $staticAnalysisProcess = $this->createStub(MutantProcess::class);
        $result = MutantExecutionResultBuilder::withMinimalTestData()
            ->withDetectionStatus($status)
            ->build()
        ;
        $testProcess->method('getMutantExecutionResult')->willReturn($result);
        $testEvaluation = MutantProcessContainer::from($mutant, static fn (): MutantProcess => $testProcess);
        $testFramework = $this->createMock(TestFramework::class);
        $testFramework->expects($this->once())->method('test')->with($this->identicalTo($mutant))->willReturn($testEvaluation);
        $created = false;
        $staticEvaluation = MutantProcessContainer::from(
            $mutant,
            static function () use (&$created, $staticAnalysisProcess): MutantProcess {
                $created = true;

                return $staticAnalysisProcess;
            },
        );
        $staticAnalysis = $this->createMock(TestFramework::class);
        $staticAnalysis->expects($this->once())->method('test')->with($this->identicalTo($mutant))->willReturn($staticEvaluation);

        $framework = new CombinedTestFramework($testFramework, $staticAnalysis);
        $evaluation = $framework->test($mutant);

        $this->assertTrue($evaluation->isCold());
        $this->assertSame($testProcess, $evaluation->getCurrent());
        $this->assertSame($hasNext, $evaluation->hasNext());
        $this->assertFalse($created);

        if ($hasNext) {
            $this->assertSame($staticAnalysisProcess, $evaluation->createNext());
            $this->assertSame($staticAnalysisProcess, $evaluation->getCurrent());
            $this->assertTrue($created);
            $this->assertFalse($evaluation->hasNext());
        }
    }

    /**
     * @param Closure(CombinedTestFramework): mixed $operation
     */
    #[DataProvider('unsupportedOperationProvider')]
    public function test_it_rejects_operations_that_still_use_the_individual_frameworks(Closure $operation): void
    {
        $framework = new CombinedTestFramework($this->createStub(TestFramework::class), null);

        $this->expectExceptionObject(new BadMethodCallException('CombinedTestFramework only supports mutant evaluation.'));

        $operation($framework);
    }

    public static function evaluationStatusProvider(): iterable
    {
        yield 'escaped' => [DetectionStatus::ESCAPED, true];

        yield 'killed by tests' => [DetectionStatus::KILLED_BY_TESTS, false];

        yield 'error' => [DetectionStatus::ERROR, false];

        yield 'timeout' => [DetectionStatus::TIMED_OUT, false];

        yield 'syntax error' => [DetectionStatus::SYNTAX_ERROR, false];
    }

    public static function unsupportedOperationProvider(): iterable
    {
        yield 'name' => [static fn (CombinedTestFramework $framework) => $framework->getName()];

        yield 'version' => [static fn (CombinedTestFramework $framework) => $framework->getVersion()];

        yield 'requirements' => [static fn (CombinedTestFramework $framework) => $framework->checkRequirements()];

        yield 'initial run' => [static fn (CombinedTestFramework $framework) => $framework->executeInitialRun()];

        yield 'JUnit availability' => [
            // @phpstan-ignore method.deprecated (The combined framework must reject this legacy API too.)
            static fn (CombinedTestFramework $framework) => $framework->hasJUnitReport(),
        ];
    }
}
