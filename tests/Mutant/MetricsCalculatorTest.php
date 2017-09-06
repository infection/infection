<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);


namespace Infection\Tests\Mutant;


use Infection\Mutant\MetricsCalculator;
use Infection\Mutant\Mutant;
use Infection\Process\MutantProcess;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use PHPUnit\Framework\TestCase;
use Mockery;
use Symfony\Component\Process\Process;

class MetricsCalculatorTest extends TestCase
{
    public function test_it_shows_zero_values_by_default()
    {
        $adapter = Mockery::mock(AbstractTestFrameworkAdapter::class);

        $calculator = new MetricsCalculator($adapter);

        $this->assertSame(0, $calculator->getEscapedCount());
        $this->assertSame(0, $calculator->getKilledCount());
        $this->assertSame(0, $calculator->getTimedOutCount());
        $this->assertSame(0, $calculator->getNotCoveredByTestsCount());
        $this->assertSame(0, $calculator->getTotalMutantsCount());
        $this->assertSame([], $calculator->getEscapedMutantProcesses());
        $this->assertSame([], $calculator->getKilledMutantProcesses());
        $this->assertSame([], $calculator->getTimedOutProcesses());
        $this->assertSame([], $calculator->getNotCoveredMutantProcesses());

        $this->assertSame(0.0, $calculator->getMutationScoreIndicator());
        $this->assertSame(0.0, $calculator->getCoverageRate());
        $this->assertSame(0.0, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    public function test_it_collects_all_values()
    {
        $adapter = Mockery::mock(AbstractTestFrameworkAdapter::class);

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

        $calculator = new MetricsCalculator($adapter);

        $calculator->collect($notCoveredMutantProcess);
        $calculator->collect($passMutantProcess);
        $calculator->collect($timedOutMutantProcess);
        $calculator->collect($killedMutantProcess);

        $this->assertSame(1, $calculator->getNotCoveredByTestsCount());
        $this->assertSame(1, $calculator->getEscapedCount());
        $this->assertSame(1, $calculator->getTimedOutCount());
        $this->assertSame(1, $calculator->getKilledCount());

        $this->assertSame(50.0, $calculator->getMutationScoreIndicator());
        $this->assertSame(75.0, $calculator->getCoverageRate());
        $this->assertSame(67.0, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    protected function tearDown()
    {
        Mockery::close();
    }
}