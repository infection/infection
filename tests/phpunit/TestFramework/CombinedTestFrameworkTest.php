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
use Infection\Process\CombinedMutantEvaluationPipe;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\CombinedTestFramework;
use Infection\TestFramework\Contracts\Mutant;
use Infection\TestFramework\Contracts\TestFramework;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CombinedTestFramework::class)]
final class CombinedTestFrameworkTest extends TestCase
{
    public function test_it_combines_the_test_framework_and_static_analysis_evaluations(): void
    {
        $mutant = $this->createStub(Mutant::class);
        $testFrameworkProcess = $this->createMock(MutantProcess::class);
        $staticAnalysisProcess = $this->createStub(MutantProcess::class);

        $testFrameworkProcess
            ->expects($this->once())
            ->method('getMutantExecutionResult')
            ->willReturn(MutantExecutionResultBuilder::withMinimalTestData()
                ->withDetectionStatus(DetectionStatus::ESCAPED)
                ->build())
        ;

        $testFramework = $this->createMock(TestFramework::class);
        $testFramework
            ->expects($this->once())
            ->method('test')
            ->with($mutant)
            ->willReturn(MutantProcessContainer::from(static fn (): MutantProcess => $testFrameworkProcess))
        ;

        $staticAnalysis = $this->createMock(TestFramework::class);
        $staticAnalysis
            ->expects($this->once())
            ->method('test')
            ->with($mutant)
            ->willReturn(MutantProcessContainer::from(static fn (): MutantProcess => $staticAnalysisProcess))
        ;

        $evaluation = (new CombinedTestFramework([$testFramework, $staticAnalysis]))->test($mutant);

        $this->assertInstanceOf(CombinedMutantEvaluationPipe::class, $evaluation);
        $this->assertSame($testFrameworkProcess, $evaluation->getCurrent());
        $this->assertTrue($evaluation->hasNext());
        $this->assertSame($staticAnalysisProcess, $evaluation->createNext());
    }
}
