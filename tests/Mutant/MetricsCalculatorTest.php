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

    private function addMutantProcess(MetricsCalculator $calculator, int $resultCode, int $count = 1): void
    {
        $mutantProcess = Mockery::mock(MutantProcessInterface::class);
        $mutantProcess->shouldReceive('getResultCode')->times($count)->andReturn($resultCode);

        while ($count--) {
            $calculator->collect($mutantProcess);
        }
    }

    public function test_it_collects_all_values(): void
    {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('stop');

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

        $this->assertSame(78.0, $calculator->getMutationScoreIndicator()); // 78.57
        $this->assertSame(92.0, $calculator->getCoverageRate()); // 92.85
        $this->assertSame(84.0, $calculator->getCoveredCodeMutationScoreIndicator()); // 84.61
    }
}
