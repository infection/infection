<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
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

namespace Infection\Tests\Process;

use Infection\Mutant\MutantInterface;
use Infection\MutationInterface;
use Infection\Mutator\Util\MutatorConfig;
use Infection\Mutator\ZeroIteration\For_;
use Infection\Process\MutantProcess;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class MutantProcessTest extends MockeryTestCase
{
    public function test_it_handles_not_covered_mutant(): void
    {
        $process = Mockery::mock(Process::class);
        $mutant = Mockery::mock(MutantInterface::class);
        $mutant->shouldReceive('isCoveredByTest')->once()->andReturn(false);
        $testFrameworkAdapter = Mockery::mock(AbstractTestFrameworkAdapter::class);

        $mutantProcess = new MutantProcess($process, $mutant, $testFrameworkAdapter);

        $this->assertSame(MutantProcess::CODE_NOT_COVERED, $mutantProcess->getResultCode());
    }

    public function test_it_handles_timeout(): void
    {
        $process = Mockery::mock(Process::class);
        $mutant = Mockery::mock(MutantInterface::class);
        $mutant->shouldReceive('isCoveredByTest')->once()->andReturn(true);
        $testFrameworkAdapter = Mockery::mock(AbstractTestFrameworkAdapter::class);

        $mutantProcess = new MutantProcess($process, $mutant, $testFrameworkAdapter);
        $mutantProcess->markTimeout();

        $this->assertSame(MutantProcess::CODE_TIMED_OUT, $mutantProcess->getResultCode());
    }

    public function test_it_handles_error(): void
    {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('getExitCode')->once()->andReturn(126);
        $mutant = Mockery::mock(MutantInterface::class);
        $mutant->shouldReceive('isCoveredByTest')->once()->andReturn(true);
        $testFrameworkAdapter = Mockery::mock(AbstractTestFrameworkAdapter::class);

        $mutantProcess = new MutantProcess($process, $mutant, $testFrameworkAdapter);

        $this->assertSame(MutantProcess::CODE_ERROR, $mutantProcess->getResultCode());
    }

    public function test_it_handles_escaped_mutant(): void
    {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('getExitCode')->once()->andReturn(0);
        $process->shouldReceive('getOutput')->once()->andReturn('...');

        $mutant = Mockery::mock(MutantInterface::class);
        $mutant->shouldReceive('isCoveredByTest')->once()->andReturn(true);

        $testFrameworkAdapter = Mockery::mock(AbstractTestFrameworkAdapter::class);
        $testFrameworkAdapter->shouldReceive('testsPass')->once()->andReturn(true);

        $mutantProcess = new MutantProcess($process, $mutant, $testFrameworkAdapter);

        $this->assertSame(MutantProcess::CODE_ESCAPED, $mutantProcess->getResultCode());
    }

    public function test_it_handles_killed_mutant(): void
    {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('getExitCode')->once()->andReturn(0);
        $process->shouldReceive('getOutput')->once()->andReturn('...');

        $mutant = Mockery::mock(MutantInterface::class);
        $mutant->shouldReceive('isCoveredByTest')->once()->andReturn(true);

        $testFrameworkAdapter = Mockery::mock(AbstractTestFrameworkAdapter::class);
        $testFrameworkAdapter->shouldReceive('testsPass')->once()->andReturn(false);

        $mutantProcess = new MutantProcess($process, $mutant, $testFrameworkAdapter);

        $this->assertSame(MutantProcess::CODE_KILLED, $mutantProcess->getResultCode());
        $this->assertSame($mutant, $mutantProcess->getMutant());
    }

    public function test_it_knows_its_mutator(): void
    {
        $mutator = new For_(new MutatorConfig([]));

        $mutation = $this->createMock(MutationInterface::class);
        $mutation->expects($this->once())->method('getMutator')->willReturn($mutator);

        $mutant = $this->createMock(MutantInterface::class);
        $mutant->expects($this->once())->method('getMutation')->willReturn($mutation);

        $adapter = $this->createMock(AbstractTestFrameworkAdapter::class);

        $process = $this->createMock(Process::class);

        $mutantProcess = new MutantProcess($process, $mutant, $adapter);

        $this->assertSame($mutator, $mutantProcess->getMutator());
    }

    public function test_it_knows_its_original_path(): void
    {
        $process = $this->createMOck(Process::class);
        $process->expects($this->never())->method($this->anything());

        $adapter = $this->createMock(AbstractTestFrameworkAdapter::class);
        $adapter->expects($this->never())->method($this->anything());

        $mutation = $this->createMock(MutationInterface::class);
        $mutation->expects($this->once())->method('getOriginalFilePath')->willReturn('foo/bar');
        $mutant = $this->createMock(MutantInterface::class);
        $mutant->expects($this->once())->method('getMutation')->willReturn($mutation);

        $mutantProcess = new MutantProcess($process, $mutant, $adapter);

        $path = $mutantProcess->getOriginalFilePath();

        $this->assertSame('foo/bar', $path);
    }

    public function test_it_knows_its_original_starting_line(): void
    {
        $process = $this->createMOck(Process::class);
        $process->expects($this->never())->method($this->anything());

        $adapter = $this->createMock(AbstractTestFrameworkAdapter::class);
        $adapter->expects($this->never())->method($this->anything());

        $mutation = $this->createMock(MutationInterface::class);
        $mutation->expects($this->once())->method('getAttributes')->willReturn(['startLine' => '3']);
        $mutant = $this->createMock(MutantInterface::class);
        $mutant->expects($this->once())->method('getMutation')->willReturn($mutation);

        $mutantProcess = new MutantProcess($process, $mutant, $adapter);

        $line = $mutantProcess->getOriginalStartingLine();

        $this->assertSame(3, $line);
    }
}
