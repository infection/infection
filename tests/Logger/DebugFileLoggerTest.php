<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
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
            'Total: 12' . PHP_EOL
            . 'Killed mutants:' . PHP_EOL
            . '===============' . PHP_EOL
            . '' . PHP_EOL
            . '' . PHP_EOL
            . 'Mutator: TrueValue' . PHP_EOL
            . 'Line 16' . PHP_EOL
            . '' . PHP_EOL
            . 'Mutator: TrueValue' . PHP_EOL
            . 'Line 17' . PHP_EOL
            . '' . PHP_EOL
            . 'Mutator: TrueValue' . PHP_EOL
            . 'Line 18' . PHP_EOL
            . '' . PHP_EOL
            . 'Mutator: TrueValue' . PHP_EOL
            . 'Line 19' . PHP_EOL
            . '' . PHP_EOL
            . 'Mutator: TrueValue' . PHP_EOL
            . 'Line 20' . PHP_EOL
            . PHP_EOL
            . 'Mutator: For_' . PHP_EOL
            . 'Line 6' . PHP_EOL
            . PHP_EOL
            . 'Mutator: For_' . PHP_EOL
            . 'Line 7' . PHP_EOL
            . PHP_EOL
            . 'Mutator: For_' . PHP_EOL
            . 'Line 8' . PHP_EOL
            . PHP_EOL
            . 'Mutator: For_' . PHP_EOL
            . 'Line 9' . PHP_EOL
            . PHP_EOL
            . 'Mutator: For_' . PHP_EOL
            . 'Line 10' . PHP_EOL
            . PHP_EOL
            . 'Errors mutants:' . PHP_EOL
            . '===============' . PHP_EOL
            . PHP_EOL
            . PHP_EOL
            . 'Escaped mutants:' . PHP_EOL
            . '================' . PHP_EOL
            . PHP_EOL
            . PHP_EOL
            . 'Mutator: For_' . PHP_EOL
            . 'Line 9' . PHP_EOL
            . PHP_EOL
            . 'Mutator: For_' . PHP_EOL
            . 'Line 10' . PHP_EOL
            . PHP_EOL
            . 'Timed Out mutants:' . PHP_EOL
            . '==================' . PHP_EOL
            . PHP_EOL
            . PHP_EOL
            . 'Not Covered mutants:' . PHP_EOL
            . '====================' . PHP_EOL
            . PHP_EOL
            . PHP_EOL
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
