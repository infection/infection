<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
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
}
