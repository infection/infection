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

namespace Infection\Tests\Logger;

use Infection\Logger\TextFileLogger;
use Infection\Mutant\MetricsCalculator;
use Infection\Mutant\MutantInterface;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\ZeroIteration\For_;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final class TextFileLoggerTest extends TestCase
{
    public function test_it_logs_correctly_with_no_mutations_and_no_debug_verbosity(): void
    {
        $logFilePath = sys_get_temp_dir() . '/foo.txt';
        $calculator = new MetricsCalculator();
        $output = $this->createMock(OutputInterface::class);

        $content = <<<'TXT'
Escaped mutants:
================

Timed Out mutants:
==================

Not Covered mutants:
====================

TXT;
        $content = str_replace("\n", PHP_EOL, $content);

        $fs = $this->createMock(Filesystem::class);
        $fs->expects($this->once())->method('dumpFile')->with(
            $logFilePath,
            $content
        );

        $debugFileLogger = new TextFileLogger($output, $logFilePath, $calculator, $fs, false, false);
        $debugFileLogger->log();
    }

    public function test_it_logs_correctly_with_no_mutations_and_debug_verbosity(): void
    {
        $logFilePath = sys_get_temp_dir() . '/foo.txt';
        $calculator = new MetricsCalculator();
        $output = $this->createMock(OutputInterface::class);
        $content = <<<'TXT'
Escaped mutants:
================

Timed Out mutants:
==================

Killed mutants:
===============

Errors mutants:
===============

Not Covered mutants:
====================

TXT;
        $content = str_replace("\n", PHP_EOL, $content);

        $fs = $this->createMock(Filesystem::class);
        $fs->expects($this->once())->method('dumpFile')->with(
            $logFilePath,
            $content
        );

        $debugFileLogger = new TextFileLogger($output, $logFilePath, $calculator, $fs, true, false);
        $debugFileLogger->log();
    }

    public function test_it_logs_correctly_with_mutations_and_no_debug_verbosity_and_with_debug(): void
    {
        $logFilePath = sys_get_temp_dir() . '/foo.txt';
        $calculator = $this->createFilledMetricsCalculator();
        $output = $this->createMock(OutputInterface::class);
        $content = <<<'TXT'
Escaped mutants:
================


1) bar/bar:16    [M] TrueValue
bin/foo/bar -c conf
Diff Diff Diff

2) bar/bar:17    [M] TrueValue
bin/foo/bar -c conf
Diff Diff Diff

3) bar/bar:18    [M] TrueValue
bin/foo/bar -c conf
Diff Diff Diff

4) bar/bar:19    [M] TrueValue
bin/foo/bar -c conf
Diff Diff Diff

5) bar/bar:20    [M] TrueValue
bin/foo/bar -c conf
Diff Diff Diff

6) foo/bar:6    [M] For_
bin/foo/bar -c conf
Diff Diff

7) foo/bar:7    [M] For_
bin/foo/bar -c conf
Diff Diff

8) foo/bar:8    [M] For_
bin/foo/bar -c conf
Diff Diff

9) foo/bar:9    [M] For_
bin/foo/bar -c conf
Diff Diff

10) foo/bar:10    [M] For_
bin/foo/bar -c conf
Diff Diff
Timed Out mutants:
==================

Not Covered mutants:
====================


1) foo/bar:9    [M] For_
bin/foo/bar -c conf
Diff Diff

2) foo/bar:10    [M] For_
bin/foo/bar -c conf
Diff Diff
TXT;
        $content = str_replace("\n", PHP_EOL, $content);
        $fs = $this->createMock(Filesystem::class);
        $fs->expects($this->atMost(10))->method('dumpFile')->with(
            $logFilePath,
            $content
        );

        $debugFileLogger = new TextFileLogger($output, $logFilePath, $calculator, $fs, false, true);
        $debugFileLogger->log();
    }

    public function test_it_logs_correctly_with_mutations_and_debug_verbosity_and_no_debug_mode(): void
    {
        $logFilePath = sys_get_temp_dir() . '/foo.txt';
        $calculator = $this->createFilledMetricsCalculator();
        $output = $this->createMock(OutputInterface::class);
        $fs = $this->createMock(Filesystem::class);
        $content = <<<'TXT'
Escaped mutants:
================


1) bar/bar:16    [M] TrueValue

Diff Diff Diff


2) bar/bar:17    [M] TrueValue

Diff Diff Diff


3) bar/bar:18    [M] TrueValue

Diff Diff Diff


4) bar/bar:19    [M] TrueValue

Diff Diff Diff


5) bar/bar:20    [M] TrueValue

Diff Diff Diff


6) foo/bar:6    [M] For_

Diff Diff


7) foo/bar:7    [M] For_

Diff Diff


8) foo/bar:8    [M] For_

Diff Diff


9) foo/bar:9    [M] For_

Diff Diff


10) foo/bar:10    [M] For_

Diff Diff

Timed Out mutants:
==================

Killed mutants:
===============


1) foo/bar:9    [M] For_

Diff Diff


2) foo/bar:10    [M] For_

Diff Diff

Errors mutants:
===============

Not Covered mutants:
====================


1) foo/bar:9    [M] For_

Diff Diff


2) foo/bar:10    [M] For_

Diff Diff

TXT;

        $content = str_replace("\n", PHP_EOL, $content);
        $fs->expects($this->atMost(10))->method('dumpFile')->with(
            $logFilePath,
            $content
        );

        $debugFileLogger = new TextFileLogger($output, $logFilePath, $calculator, $fs, true, false);
        $debugFileLogger->log();
    }

    public function test_it_rejects_invalid_streams_with_a_clear_error_message(): void
    {
        $logFilePath = 'php://memory';
        $calculator = new MetricsCalculator();
        $fs = $this->createMock(Filesystem::class);
        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())->method('writeln')->with('<error>The only streams supported are php://stdout and php://stderr</error>');

        $debugFileLogger = new TextFileLogger($output, $logFilePath, $calculator, $fs, false, false);
        $debugFileLogger->log();
    }

    private function createFilledMetricsCalculator(): MetricsCalculator
    {
        $processes = [];

        for ($i = 0; $i < 5; ++$i) {
            $phpProcess = $this->createMock(Process::class);
            $phpProcess->expects($this->atMost(1))->method('getCommandLine')->willReturn('bin/foo/bar -c conf');
            $phpProcess->expects($this->atMost(1))->method('isStarted')->willReturn(true);

            $mutant = $this->createMock(MutantInterface::class);
            $mutant->expects($this->once())->method('getDiff')->willReturn('Diff Diff');

            $process = $this->createMock(MutantProcessInterface::class);
            $process->method('getProcess')->willReturn($phpProcess);
            $process->expects($this->once())->method('getMutant')->willReturn($mutant);

            $process->expects($this->once())->method('getMutatorClass')->willReturn(For_::class);
            $process->expects($this->once())->method('getResultCode')->willReturn(MutantProcess::CODE_ESCAPED);
            $process->expects($this->atLeast(2))->method('getOriginalStartingLine')->willReturn(10 - $i);
            $process->expects($this->atLeast(1))->method('getOriginalFilePath')->willReturn('foo/bar');
            $processes[] = $process;
        }

        for ($i = 0; $i < 5; ++$i) {
            $phpProcess = $this->createMock(Process::class);

            $phpProcess->expects($this->atMost(1))->method('getCommandLine')->willReturn('bin/foo/bar -c conf');
            $phpProcess->expects($this->atMost(1))->method('isStarted')->willReturn(true);

            $mutant = $this->createMock(MutantInterface::class);
            $mutant->expects($this->once())->method('getDiff')->willReturn('Diff Diff Diff');

            $process = $this->createMock(MutantProcessInterface::class);
            $process->method('getProcess')->willReturn($phpProcess);
            $process->expects($this->once())->method('getMutant')->willReturn($mutant);

            $process->expects($this->once())->method('getMutatorClass')->willReturn(TrueValue::class);
            $process->expects($this->once())->method('getResultCode')->willReturn(MutantProcess::CODE_ESCAPED);
            $process->expects($this->atLeast(2))->method('getOriginalStartingLine')->willReturn(20 - $i);
            $process->expects($this->atLeast(1))->method('getOriginalFilePath')->willReturn('bar/bar');

            $processes[] = $process;
        }

        for ($i = 0; $i < 2; ++$i) {
            $phpProcess = $this->createMock(Process::class);

            $phpProcess->expects($this->atMost(1))->method('getCommandLine')->willReturn('bin/foo/bar -c conf');
            $phpProcess->expects($this->atMost(1))->method('isStarted')->willReturn(true);

            $mutant = $this->createMock(MutantInterface::class);
            $mutant->expects($this->once())->method('getDiff')->willReturn('Diff Diff');

            $process = $this->createMock(MutantProcessInterface::class);
            $process->method('getProcess')->willReturn($phpProcess);
            $process->expects($this->once())->method('getMutant')->willReturn($mutant);

            $process->expects($this->once())->method('getMutatorClass')->willReturn(For_::class);
            $process->expects($this->once())->method('getResultCode')->willReturn(MutantProcess::CODE_NOT_COVERED);
            $process->expects($this->atLeast(2))->method('getOriginalStartingLine')->willReturn(10 - $i);
            $process->expects($this->atLeast(1))->method('getOriginalFilePath')->willReturn('foo/bar');
            $processes[] = $process;
        }

        for ($i = 0; $i < 2; ++$i) {
            $phpProcess = $this->createMock(Process::class);

            $phpProcess->expects($this->atMost(1))->method('getCommandLine')->willReturn('bin/foo/bar -c conf');
            $phpProcess->expects($this->atMost(1))->method('isStarted')->willReturn(true);

            $mutant = $this->createMock(MutantInterface::class);
            $mutant->expects($this->atMost(1))->method('getDiff')->willReturn('Diff Diff');

            $process = $this->createMock(MutantProcessInterface::class);
            $process->method('getProcess')->willReturn($phpProcess);
            $process->expects($this->atMost(1))->method('getMutant')->willReturn($mutant);

            $process->expects($this->atMost(1))->method('getMutatorClass')->willReturn(For_::class);
            $process->expects($this->atMost(1))->method('getResultCode')->willReturn(MutantProcess::CODE_KILLED);
            $process->method('getOriginalStartingLine')->willReturn(10 - $i);
            $process->method('getOriginalFilePath')->willReturn('foo/bar');
            $processes[] = $process;
        }

        return MetricsCalculator::createFromArray($processes);
    }
}
