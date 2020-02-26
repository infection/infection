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

namespace Infection\Tests\Mutant;

use Infection\Mutant\MetricsCalculator;
use Infection\Process\MutantProcess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class MetricsCalculatorTest extends TestCase
{
    public function test_it_shows_zero_values_by_default(): void
    {
        $calculator = new MetricsCalculator();

        $this->assertSame(0, $calculator->getEscapedCount());
        $this->assertSame(0, $calculator->getKilledCount());
        $this->assertSame(0, $calculator->getErrorCount());
        $this->assertSame(0, $calculator->getTimedOutCount());
        $this->assertSame(0, $calculator->getNotCoveredByTestsCount());
        $this->assertSame(0, $calculator->getTotalMutantsCount());
        $this->assertSame([], $calculator->getEscapedMutantExecutionResults());
        $this->assertSame([], $calculator->getKilledMutantExecutionResults());
        $this->assertSame([], $calculator->getErrorMutantExecutionResults());
        $this->assertSame([], $calculator->getTimedOutMutantExecutionResults());
        $this->assertSame([], $calculator->getNotCoveredMutantExecutionResults());

        $this->assertSame(0.0, $calculator->getMutationScoreIndicator());
        $this->assertSame(0.0, $calculator->getCoverageRate());
        $this->assertSame(0.0, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    public function test_it_collects_all_values(): void
    {
        $process = $this->createMock(Process::class);
        $process->method('stop');

        $calculator = new MetricsCalculator();

        $this->addMutantProcess($calculator, MutantProcess::CODE_NOT_COVERED);
        $this->assertSame(1, $calculator->getNotCoveredByTestsCount());

        $this->addMutantProcess($calculator, MutantProcess::CODE_ESCAPED, 2);
        $this->assertSame(2, $calculator->getEscapedCount());

        $this->addMutantProcess($calculator, MutantProcess::CODE_TIMED_OUT, 2);
        $this->assertSame(2, $calculator->getTimedOutCount());

        $this->addMutantProcess($calculator, MutantProcess::CODE_KILLED, 7);
        $this->assertSame(7, $calculator->getKilledCount());

        $this->addMutantProcess($calculator, MutantProcess::CODE_ERROR, 2);
        $this->assertSame(2, $calculator->getErrorCount());

        $this->assertSame(78.57142857142857, $calculator->getMutationScoreIndicator());
        $this->assertSame(92.85714285714286, $calculator->getCoverageRate());
        $this->assertSame(84.61538461538461, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    private function addMutantProcess(MetricsCalculator $calculator, int $resultCode, int $count = 1): void
    {
        $mutantProcess = $this->createMock(MutantProcess::class);
        $mutantProcess->expects($this->exactly($count))
            ->method('getResultCode')
            ->willReturn($resultCode);

        while ($count--) {
            $calculator->collect($mutantProcess);
        }
    }
}
