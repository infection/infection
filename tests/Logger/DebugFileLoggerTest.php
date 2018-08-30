<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Logger;

use Infection\Logger\DebugFileLogger;
use Infection\Mutant\MetricsCalculator;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\Util\MutatorConfig;
use Infection\Mutator\ZeroIteration\For_;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class DebugFileLoggerTest extends TestCase
{
    public function test_it_logs_correctly_with_no_mutations(): void
    {
        $logFilePath = sys_get_temp_dir() . '/foo.txt';
        $calculator = new MetricsCalculator();
        $output = $this->createMock(OutputInterface::class);
        $fs = $this->createMock(Filesystem::class);
        $fs->expects($this->once())->method('dumpFile')->with(
            $logFilePath,
            <<<'TXT'
Total: 0
Killed mutants:
===============


Errors mutants:
===============


Escaped mutants:
================


Timed Out mutants:
==================


Not Covered mutants:
====================


TXT
        );

        $debugFileLogger = new DebugFileLogger($output, $logFilePath, $calculator, $fs, false, false);
        $debugFileLogger->log();
    }

    public function test_it_log_correctly_with_mutations(): void
    {
        $logFilePath = sys_get_temp_dir() . '/foo.txt';
        $calculator = $this->createFilledMetricsCalculator();
        $output = $this->createMock(OutputInterface::class);
        $fs = $this->createMock(Filesystem::class);
        $fs->expects($this->once())->method('dumpFile')->with(
            $logFilePath,
            <<<'TXT'
Total: 12
Killed mutants:
===============


Mutator: TrueValue
Line 16

Mutator: TrueValue
Line 17

Mutator: TrueValue
Line 18

Mutator: TrueValue
Line 19

Mutator: TrueValue
Line 20

Mutator: For_
Line 6

Mutator: For_
Line 7

Mutator: For_
Line 8

Mutator: For_
Line 9

Mutator: For_
Line 10

Errors mutants:
===============


Escaped mutants:
================


Mutator: For_
Line 9

Mutator: For_
Line 10

Timed Out mutants:
==================


Not Covered mutants:
====================


TXT
        );

        $debugFileLogger = new DebugFileLogger($output, $logFilePath, $calculator, $fs, false, false);
        $debugFileLogger->log();
    }

    private function createFilledMetricsCalculator(): MetricsCalculator
    {
        $processes = [];

        for ($i = 0; $i < 5; ++$i) {
            $process = $this->createMock(MutantProcessInterface::class);
            $process->expects($this->once())->method('getMutator')->willReturn(new For_(new MutatorConfig([])));
            $process->expects($this->once())->method('getResultCode')->willReturn(MutantProcess::CODE_KILLED);
            $process->expects($this->atLeast(2))->method('getOriginalStartingLine')->willReturn(10 - $i);
            $process->expects($this->atLeast(1))->method('getOriginalFilePath')->willReturn('foo/bar');
            $processes[] = $process;
        }

        for ($i = 0; $i < 5; ++$i) {
            $process = $this->createMock(MutantProcessInterface::class);
            $process->expects($this->once())->method('getMutator')->willReturn(new TrueValue(new MutatorConfig([])));
            $process->expects($this->once())->method('getResultCode')->willReturn(MutantProcess::CODE_KILLED);
            $process->expects($this->atLeast(2))->method('getOriginalStartingLine')->willReturn(20 - $i);
            $process->expects($this->atLeast(1))->method('getOriginalFilePath')->willReturn('bar/bar');

            $processes[] = $process;
        }

        for ($i = 0; $i < 2; ++$i) {
            $process = $this->createMock(MutantProcessInterface::class);
            $process->expects($this->once())->method('getMutator')->willReturn(new For_(new MutatorConfig([])));
            $process->expects($this->once())->method('getResultCode')->willReturn(MutantProcess::CODE_ESCAPED);
            $process->expects($this->atLeast(2))->method('getOriginalStartingLine')->willReturn(10 - $i);
            $process->expects($this->atLeast(1))->method('getOriginalFilePath')->willReturn('foo/bar');
            $processes[] = $process;
        }

        return MetricsCalculator::createFromArray($processes);
    }
}
