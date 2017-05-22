<?php

declare(strict_types=1);


namespace Mutant;


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

        $this->assertSame(0.0, $calculator->getMutationScoreIndicator());
        $this->assertSame(0.0, $calculator->getCoverageRate());
        $this->assertSame(0.0, $calculator->getCoveredCodeMutationScoreIndicator());
    }

    public function test_it_collects_all_values()
    {
        $adapter = Mockery::mock(AbstractTestFrameworkAdapter::class);
        $adapter->shouldReceive('testsPass')->times(1)->andReturn(true);
        $adapter->shouldReceive('testsPass')->times(2)->andReturn(false);

        $process = Mockery::mock(Process::class);
        $process->shouldReceive('getOutput')->times(3)->andReturn('');
        $process->shouldReceive('stop');

        $mutant = Mockery::mock(Mutant::class);
        $mutant->shouldReceive('isCoveredByTest')->once()->andReturn(false);
        $mutant->shouldReceive('isCoveredByTest')->times(3)->andReturn(true);

        $notCoveredMutantProcess = Mockery::mock(MutantProcess::class);
        $notCoveredMutantProcess->shouldReceive('getMutant')->andReturn($mutant);

        $passMutantProcess = Mockery::mock(MutantProcess::class);
        $passMutantProcess->shouldReceive('getProcess')->andReturn($process);
        $passMutantProcess->shouldReceive('getMutant')->andReturn($mutant);

        $timedOutMutantProcess = Mockery::mock(MutantProcess::class);
        $timedOutMutantProcess->shouldReceive('getProcess')->andReturn($process);
        $timedOutMutantProcess->shouldReceive('getMutant')->andReturn($mutant);
        $timedOutMutantProcess->shouldReceive('isTimedOut')->andReturn(true);

        $killedMutantProcess = Mockery::mock(MutantProcess::class);
        $killedMutantProcess->shouldReceive('getProcess')->andReturn($process);
        $killedMutantProcess->shouldReceive('getMutant')->andReturn($mutant);
        $killedMutantProcess->shouldReceive('isTimedOut')->andReturn(false);

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