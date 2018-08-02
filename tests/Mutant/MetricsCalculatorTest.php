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
use Infection\Process\MutantProcessInterface;
use Mockery;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class MetricsCalculatorTest extends Mockery\Adapter\Phpunit\MockeryTestCase
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
        $this->assertSame([], $calculator->getEscapedMutantProcesses());
        $this->assertSame([], $calculator->getKilledMutantProcesses());
        $this->assertSame([], $calculator->getErrorProcesses());
        $this->assertSame([], $calculator->getTimedOutProcesses());
        $this->assertSame([], $calculator->getNotCoveredMutantProcesses());

        $this->assertSame(0.0, $calculator->getMutationScoreIndicator());
        $this->assertSame(0.0, $calculator->getCoverageRate());
        $this->assertSame(0.0, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    public function test_it_collects_all_values(): void
    {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('stop');

        $notCoveredMutantProcess = Mockery::mock(MutantProcessInterface::class);
        $notCoveredMutantProcess->shouldReceive('getResultCode')->times(1)->andReturn(MutantProcess::CODE_NOT_COVERED);

        $passMutantProcess = Mockery::mock(MutantProcessInterface::class);
        $passMutantProcess->shouldReceive('getResultCode')->times(1)->andReturn(MutantProcess::CODE_ESCAPED);

        $timedOutMutantProcess = Mockery::mock(MutantProcessInterface::class);
        $timedOutMutantProcess->shouldReceive('getResultCode')->times(1)->andReturn(MutantProcess::CODE_TIMED_OUT);

        $killedMutantProcess = Mockery::mock(MutantProcessInterface::class);
        $killedMutantProcess->shouldReceive('getResultCode')->times(1)->andReturn(MutantProcess::CODE_KILLED);

        $errorMutantProcess = Mockery::mock(MutantProcessInterface::class);
        $errorMutantProcess->shouldReceive('getResultCode')->times(2)->andReturn(MutantProcess::CODE_ERROR);

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

        $calculator->collect($errorMutantProcess);
        $this->assertSame(66.0, $calculator->getMutationScoreIndicator()); // (1+1+2)/6 = 66.66%
    }
}
