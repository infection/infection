<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class MutantProcessTest extends TestCase
{
    /**
     * @var MutantProcess
     */
    private $mutantProcess;

    /**
     * @var MockObject|Process
     */
    private $process;

    /**
     * @var MockObject|MutantInterface
     */
    private $mutant;

    /**
     * @var MockObject|AbstractTestFrameworkAdapter
     */
    private $adapter;

    protected function setUp(): void
    {
        $this->process = $this->createMock(Process::class);
        $this->mutant = $this->createMock(MutantInterface::class);
        $this->adapter = $this->createMock(AbstractTestFrameworkAdapter::class);

        $this->mutantProcess = new MutantProcess($this->process, $this->mutant, $this->adapter);
    }

    public function test_it_handles_not_covered_mutant(): void
    {
        $this->mutant
            ->expects($this->once())
            ->method('isCoveredByTest')
            ->willReturn(false);

        $this->assertSame(MutantProcess::CODE_NOT_COVERED, $this->mutantProcess->getResultCode());
    }

    public function test_it_handles_timeout(): void
    {
        $this->mutant
            ->expects($this->once())
            ->method('isCoveredByTest')
            ->willReturn(true);

        $this->mutantProcess->markTimeout();

        $this->assertSame(MutantProcess::CODE_TIMED_OUT, $this->mutantProcess->getResultCode());
    }

    public function test_it_handles_error(): void
    {
        $this->mutant
            ->expects($this->once())
            ->method('isCoveredByTest')
            ->willReturn(true);

        $this->process
            ->expects($this->once())
            ->method('getExitCode')
            ->willReturn(126);

        $this->assertSame(MutantProcess::CODE_ERROR, $this->mutantProcess->getResultCode());
    }

    public function test_it_handles_escaped_mutant(): void
    {
        $this->mutant
            ->expects($this->once())
            ->method('isCoveredByTest')
            ->willReturn(true);

        $this->process
            ->expects($this->once())
            ->method('getExitCode')
            ->willReturn(0);

        $this->process
            ->expects($this->once())
            ->method('getOutput')
            ->willReturn('...');

        $this->adapter
            ->expects($this->once())
            ->method('testsPass')
            ->willReturn(true);

        $this->assertSame(MutantProcess::CODE_ESCAPED, $this->mutantProcess->getResultCode());
    }

    public function test_it_handles_killed_mutant(): void
    {
        $this->mutant
            ->expects($this->once())
            ->method('isCoveredByTest')
            ->willReturn(true);

        $this->process
            ->expects($this->once())
            ->method('getExitCode')
            ->willReturn(0);

        $this->process
            ->expects($this->once())
            ->method('getOutput')
            ->willReturn('...');

        $this->adapter
            ->expects($this->once())
            ->method('testsPass')
            ->willReturn(false);

        $this->assertSame(MutantProcess::CODE_KILLED, $this->mutantProcess->getResultCode());
        $this->assertSame($this->mutant, $this->mutantProcess->getMutant());
    }

    public function test_it_knows_its_mutator(): void
    {
        $mutator = new For_(new MutatorConfig([]));

        $mutation = $this->createMock(MutationInterface::class);
        $mutation->expects($this->once())
            ->method('getMutator')
            ->willReturn($mutator);

        $this->mutant
            ->expects($this->once())
            ->method('getMutation')
            ->willReturn($mutation);

        $this->assertSame($mutator, $this->mutantProcess->getMutator());
    }

    public function test_it_knows_its_original_path(): void
    {
        $this->process
            ->expects($this->never())
            ->method($this->anything());

        $this->adapter
            ->expects($this->never())
            ->method($this->anything());

        $mutation = $this->createMock(MutationInterface::class);
        $mutation->expects($this->once())
            ->method('getOriginalFilePath')
            ->willReturn('foo/bar');

        $this->mutant
            ->expects($this->once())
            ->method('getMutation')
            ->willReturn($mutation);

        $this->assertSame('foo/bar', $this->mutantProcess->getOriginalFilePath());
    }

    public function test_it_knows_its_original_starting_line(): void
    {
        $this->process
            ->expects($this->never())
            ->method($this->anything());

        $this->adapter
            ->expects($this->never())
            ->method($this->anything());

        $mutation = $this->createMock(MutationInterface::class);
        $mutation->expects($this->once())
            ->method('getAttributes')
            ->willReturn(['startLine' => '3']);

        $this->mutant
            ->expects($this->once())
            ->method('getMutation')
            ->willReturn($mutation);

        $this->assertSame(3, $this->mutantProcess->getOriginalStartingLine());
    }
}
