<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutant;

use Infection\Mutant\MetricsCalculator;
use Infection\Process\MutantProcess;
use Mockery;
use Symfony\Component\Process\Process;

class MetricsCalculatorTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    public function test_it_shows_zero_values_by_default()
    {
        $calculator = new MetricsCalculator();

        $this->assertSame(0, $calculator->getEscapedCount());
        $this->assertSame(0, $calculator->getKilledCount());
        $this->assertSame(0, $calculator->getErrorCount());
        $this->assertSame(0, $calculator->getTimedOutCount());
        $this->assertSame(0, $calculator->getNotCoveredByTestsCount());
        $this->assertSame(0, $calculator->getTotalMutantsCount());
        $this->assertSame([], $calculator->getEscapedMutantProcesses());
        $this->assertSame([], $calculator->getKilledMutantProcesses());
        $this->assertSame([], $calculator->getErrorProcesses());
        $this->assertSame([], $calculator->getTimedOutProcesses());
        $this->assertSame([], $calculator->getNotCoveredMutantProcesses());

        $this->assertSame(0.0, $calculator->getMutationScoreIndicator());
        $this->assertSame(0.0, $calculator->getCoverageRate());
        $this->assertSame(0.0, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    public function test_it_collects_all_values()
    {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('stop');

        $notCoveredMutantProcess = Mockery::mock(MutantProcess::class);
        $notCoveredMutantProcess->shouldReceive('getResultCode')->times(1)->andReturn(MutantProcess::CODE_NOT_COVERED);

        $passMutantProcess = Mockery::mock(MutantProcess::class);
        $passMutantProcess->shouldReceive('getResultCode')->times(1)->andReturn(MutantProcess::CODE_ESCAPED);

        $timedOutMutantProcess = Mockery::mock(MutantProcess::class);
        $timedOutMutantProcess->shouldReceive('getResultCode')->times(1)->andReturn(MutantProcess::CODE_TIMED_OUT);

        $killedMutantProcess = Mockery::mock(MutantProcess::class);
        $killedMutantProcess->shouldReceive('getResultCode')->times(1)->andReturn(MutantProcess::CODE_KILLED);

        $errorMutantProcess = Mockery::mock(MutantProcess::class);
        $errorMutantProcess->shouldReceive('getResultCode')->times(1)->andReturn(MutantProcess::CODE_ERROR);

        $calculator = new MetricsCalculator();

        $calculator->collect($notCoveredMutantProcess);
        $calculator->collect($passMutantProcess);
        $calculator->collect($timedOutMutantProcess);
        $calculator->collect($killedMutantProcess);
        $calculator->collect($errorMutantProcess);

        $this->assertSame(1, $calculator->getNotCoveredByTestsCount());
        $this->assertSame(1, $calculator->getEscapedCount());
        $this->assertSame(1, $calculator->getTimedOutCount());
        $this->assertSame(1, $calculator->getKilledCount());
        $this->assertSame(1, $calculator->getErrorCount());

        $this->assertSame(60.0, $calculator->getMutationScoreIndicator());
        $this->assertSame(80.0, $calculator->getCoverageRate());
        $this->assertSame(75.0, $calculator->getCoveredCodeMutationScoreIndicator());
    }
}
