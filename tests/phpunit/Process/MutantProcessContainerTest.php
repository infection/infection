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

namespace Infection\Tests\Process;

use Infection\Mutant\DetectionStatus;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantExecutionResult;
use Infection\Process\Factory\LazyMutantProcessFactory;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\StaticAnalysis\PHPStan\Mutant\PHPStanMutantExecutionResultFactory;
use Infection\StaticAnalysis\PHPStan\Process\PHPStanMutantProcessFactory;
use Infection\TestFramework\CommandLineBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(MutantProcessContainer::class)]
final class MutantProcessContainerTest extends TestCase
{
    /**
     * @var MutantProcess|MockObject
     */
    private $phpUnitMutantProcess;

    /**
     * @var Mutant|MockObject
     */
    private $mutant;

    /**
     * @var MutantExecutionResult|MockObject
     */
    private $mutantExecutionResult;

    /**
     * @var LazyMutantProcessFactory|MockObject
     */
    private $lazyMutantProcessCreator;

    protected function setUp(): void
    {
        $this->mutant = $this->createMock(Mutant::class);
        $this->mutantExecutionResult = $this->createMock(MutantExecutionResult::class);
        $this->phpUnitMutantProcess = $this->createMock(MutantProcess::class);
        $this->lazyMutantProcessCreator = $this->createMock(LazyMutantProcessFactory::class);
    }

    public function test_it_returns_false_when_there_is_no_next_process_to_kill_mutant(): void
    {
        $container = new MutantProcessContainer($this->phpUnitMutantProcess, []);

        $this->assertFalse($container->hasNext());
    }

    public function test_it_returns_false_when_next_process_does_not_support_mutant(): void
    {
        $container = new MutantProcessContainer($this->phpUnitMutantProcess, [
            new PHPStanMutantProcessFactory(
                $this->createMock(PHPStanMutantExecutionResultFactory::class),
                '/path/to/phpstan',
                $this->createMock(CommandLineBuilder::class),
                100.0,
            ),
        ]);

        $this->assertFalse($container->hasNext());
    }

    public function test_it_returns_true_when_next_process_supports_mutant(): void
    {
        $this->phpUnitMutantProcess
            ->expects($this->once())
            ->method('getMutant')
            ->willReturn($this->mutant);

        $this->phpUnitMutantProcess
            ->expects($this->once())
            ->method('getMutantExecutionResult')
            ->willReturn($this->mutantExecutionResult);

        $this->mutantExecutionResult
            ->expects($this->once())
            ->method('getDetectionStatus')
            ->willReturn(DetectionStatus::ESCAPED);

        $mutantProcessFactoryMock = $this->createMock(LazyMutantProcessFactory::class);

        $mutantProcessFactoryMock
            ->expects($this->once())
            ->method('supports')
            ->willReturn(true);

        $container = new MutantProcessContainer(
            $this->phpUnitMutantProcess,
            [$mutantProcessFactoryMock],
        );

        $this->assertTrue($container->hasNext());
    }

    public function test_it_returns_true_when_there_is_next_process_to_kill_mutant(): void
    {
        $container = new MutantProcessContainer(
            $this->phpUnitMutantProcess,
            [$this->lazyMutantProcessCreator],
        );

        // Build the first next process to advance the index
        $newMutantProcess = $this->createMock(MutantProcess::class);

        $this->phpUnitMutantProcess
            ->expects($this->exactly(2))
            ->method('getMutant')
            ->willReturn($this->mutant);

        $this->phpUnitMutantProcess
            ->expects($this->once())
            ->method('getMutantExecutionResult')
            ->willReturn($this->mutantExecutionResult);

        $this->mutantExecutionResult
            ->expects($this->once())
            ->method('getDetectionStatus')
            ->willReturn(DetectionStatus::ESCAPED);

        $this->lazyMutantProcessCreator
            ->expects($this->once())
            ->method('create')
            ->with($this->mutant)
            ->willReturn($newMutantProcess);

        $this->lazyMutantProcessCreator
            ->expects($this->once())
            ->method('supports')
            ->with($this->mutant)
            ->willReturn(true);

        $this->assertTrue($container->hasNext());

        $container->createNext();

        $this->assertFalse($container->hasNext());
    }

    public function test_it_builds_next_process_to_kill_mutant(): void
    {
        $container = new MutantProcessContainer(
            $this->phpUnitMutantProcess,
            [$this->lazyMutantProcessCreator],
        );

        $newMutantProcess = $this->createMock(MutantProcess::class);

        $this->phpUnitMutantProcess
            ->expects($this->once())
            ->method('getMutant')
            ->willReturn($this->mutant);

        $this->lazyMutantProcessCreator
            ->expects($this->once())
            ->method('create')
            ->with($this->mutant)
            ->willReturn($newMutantProcess);

        $result = $container->createNext();

        $this->assertSame($newMutantProcess, $result);
    }

    public function test_it_returns_current_mutant_process(): void
    {
        $container = new MutantProcessContainer($this->phpUnitMutantProcess, []);

        $this->assertSame($this->phpUnitMutantProcess, $container->getCurrent());
    }

    public function test_it_returns_next_mutant_process_after_building_it(): void
    {
        $container = new MutantProcessContainer(
            $this->phpUnitMutantProcess,
            [$this->lazyMutantProcessCreator],
        );

        $newMutantProcess = $this->createMock(MutantProcess::class);

        $this->phpUnitMutantProcess
            ->expects($this->once())
            ->method('getMutant')
            ->willReturn($this->mutant);

        $this->lazyMutantProcessCreator
            ->expects($this->once())
            ->method('create')
            ->with($this->mutant)
            ->willReturn($newMutantProcess);

        $container->createNext();

        $this->assertSame($newMutantProcess, $container->getCurrent());
    }
}
