<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Process;

use Infection\Mutant\Mutant;
use Infection\Process\MutantProcess;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Process\Process;

class MutantProcessTest extends MockeryTestCase
{
    public function test_it_handles_not_covered_mutant()
    {
        $process = Mockery::mock(Process::class);
        $mutant = Mockery::mock(Mutant::class);
        $mutant->shouldReceive('isCoveredByTest')->once()->andReturn(false);
        $testFrameworkAdapter = Mockery::mock(AbstractTestFrameworkAdapter::class);

        $mutantProcess = new MutantProcess($process, $mutant, $testFrameworkAdapter);

        $this->assertSame(MutantProcess::CODE_NOT_COVERED, $mutantProcess->getResultCode());
    }

    public function test_it_handles_timeout()
    {
        $process = Mockery::mock(Process::class);
        $mutant = Mockery::mock(Mutant::class);
        $mutant->shouldReceive('isCoveredByTest')->once()->andReturn(true);
        $testFrameworkAdapter = Mockery::mock(AbstractTestFrameworkAdapter::class);

        $mutantProcess = new MutantProcess($process, $mutant, $testFrameworkAdapter);
        $mutantProcess->markTimeout();

        $this->assertSame(MutantProcess::CODE_TIMED_OUT, $mutantProcess->getResultCode());
    }

    public function test_it_handles_error()
    {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('getExitCode')->once()->andReturn(126);
        $mutant = Mockery::mock(Mutant::class);
        $mutant->shouldReceive('isCoveredByTest')->once()->andReturn(true);
        $testFrameworkAdapter = Mockery::mock(AbstractTestFrameworkAdapter::class);

        $mutantProcess = new MutantProcess($process, $mutant, $testFrameworkAdapter);

        $this->assertSame(MutantProcess::CODE_ERROR, $mutantProcess->getResultCode());
    }

    public function test_it_handles_escaped_mutant()
    {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('getExitCode')->once()->andReturn(0);
        $process->shouldReceive('getOutput')->once()->andReturn('...');

        $mutant = Mockery::mock(Mutant::class);
        $mutant->shouldReceive('isCoveredByTest')->once()->andReturn(true);

        $testFrameworkAdapter = Mockery::mock(AbstractTestFrameworkAdapter::class);
        $testFrameworkAdapter->shouldReceive('testsPass')->once()->andReturn(true);

        $mutantProcess = new MutantProcess($process, $mutant, $testFrameworkAdapter);

        $this->assertSame(MutantProcess::CODE_ESCAPED, $mutantProcess->getResultCode());
    }

    public function test_it_handles_killed_mutant()
    {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('getExitCode')->once()->andReturn(0);
        $process->shouldReceive('getOutput')->once()->andReturn('...');

        $mutant = Mockery::mock(Mutant::class);
        $mutant->shouldReceive('isCoveredByTest')->once()->andReturn(true);

        $testFrameworkAdapter = Mockery::mock(AbstractTestFrameworkAdapter::class);
        $testFrameworkAdapter->shouldReceive('testsPass')->once()->andReturn(false);

        $mutantProcess = new MutantProcess($process, $mutant, $testFrameworkAdapter);

        $this->assertSame(MutantProcess::CODE_KILLED, $mutantProcess->getResultCode());
        $this->assertSame($mutant, $mutantProcess->getMutant());
    }
}