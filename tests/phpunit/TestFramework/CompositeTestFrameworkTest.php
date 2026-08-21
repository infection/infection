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

use Infection\Mutant\DetectionStatus;
use Infection\Mutant\Mutant;
use Infection\Process\MutantProcess;
use Infection\TestFramework\Common\LazyMutantEvaluationPipe;
use Infection\TestFramework\CompositeTestFramework;
use Infection\TestFramework\Contracts\CompositeInitialRunResults;
use Infection\TestFramework\Contracts\InitialRunResults;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(CompositeTestFramework::class)]
final class CompositeTestFrameworkTest extends TestCase
{
    private MockObject&TestFramework $firstTestFramework;

    private MockObject&TestFramework $secondTestFramework;

    private CompositeTestFramework $testFramework;

    protected function setUp(): void
    {
        $this->firstTestFramework = $this->createMock(TestFramework::class);
        $this->secondTestFramework = $this->createMock(TestFramework::class);
        $this->testFramework = new CompositeTestFramework([
            $this->firstTestFramework,
            $this->secondTestFramework,
        ]);
    }

    public function test_it_describes_the_aggregated_test_frameworks(): void
    {
        $this->firstTestFramework->method('getName')->willReturn('PHPUnit');
        $this->secondTestFramework->method('getName')->willReturn('PHPStan');
        $this->firstTestFramework->method('getVersion')->willReturn('12.0.0');
        $this->secondTestFramework->method('getVersion')->willReturn('2.1.0');
        $this->firstTestFramework->method('getBinary')->willReturn('/project/vendor/bin/phpunit');
        $this->secondTestFramework->method('getBinary')->willReturn('/project/vendor/bin/phpstan');

        $this->assertSame('Composite(PHPUnit, PHPStan)', $this->testFramework->getName());
        $this->assertSame('12.0.0, 2.1.0', $this->testFramework->getVersion());
        $this->assertSame(
            '/project/vendor/bin/phpunit, /project/vendor/bin/phpstan',
            $this->testFramework->getBinary(),
        );
    }

    public function test_it_checks_each_test_framework_requirements(): void
    {
        $this->firstTestFramework->expects($this->once())->method('checkRequirements');
        $this->secondTestFramework->expects($this->once())->method('checkRequirements');

        $this->testFramework->checkRequirements();
    }

    public function test_it_exposes_the_aggregated_test_frameworks(): void
    {
        $this->assertSame(
            [$this->firstTestFramework, $this->secondTestFramework],
            $this->testFramework->getTestFrameworks(),
        );
    }

    public function test_it_executes_each_initial_run(): void
    {
        $firstResult = new InitialRunResults('PHPUnit output');
        $secondResult = new InitialRunResults('PHPStan output');
        $this->firstTestFramework->expects($this->once())->method('executeInitialRun')->willReturn($firstResult);
        $this->secondTestFramework->expects($this->once())->method('executeInitialRun')->willReturn($secondResult);

        $this->assertEquals(
            new CompositeInitialRunResults([
                [$this->firstTestFramework, $firstResult],
                [$this->secondTestFramework, $secondResult],
            ]),
            $this->testFramework->executeInitialRun(),
        );
    }

    public function test_it_evaluates_a_mutant_with_each_test_framework_until_it_is_killed(): void
    {
        $mutant = $this->createMock(Mutant::class);
        $firstProcess = $this->createMock(MutantProcess::class);
        $secondProcess = $this->createMock(MutantProcess::class);
        $firstProcess
            ->method('getMutantExecutionResult')
            ->willReturn(MutantExecutionResultBuilder::withMinimalTestData()
                ->withDetectionStatus(DetectionStatus::ESCAPED)
                ->build());
        $this->firstTestFramework
            ->expects($this->once())
            ->method('test')
            ->with($mutant)
            ->willReturn(new LazyMutantEvaluationPipe(static fn () => $firstProcess));
        $this->secondTestFramework
            ->expects($this->once())
            ->method('test')
            ->with($mutant)
            ->willReturn(new LazyMutantEvaluationPipe(static fn () => $secondProcess));

        $pipe = $this->testFramework->test($mutant);

        $this->assertSame($firstProcess, $pipe->getCurrent());
        $this->assertTrue($pipe->hasNext());
        $this->assertSame($secondProcess, $pipe->createNext());
    }
}
