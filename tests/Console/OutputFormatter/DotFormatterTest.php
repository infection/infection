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

namespace Infection\Tests\Console\OutputFormatter;

use Infection\Console\OutputFormatter\DotFormatter;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

final class DotFormatterTest extends TestCase
{
    public function test_start_logs_inital_starting_text(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())->method('writeln')->with([
            '',
            '<killed>.</killed>: killed, '
            . '<escaped>M</escaped>: escaped, '
            . '<uncovered>S</uncovered>: uncovered, '
            . '<with-error>E</with-error>: fatal error, '
            . '<timeout>T</timeout>: timed out',
            '',
        ]);

        $formatter = new DotFormatter($output);
        $formatter->start(10);
    }

    public function test_killed_logs_correctly_in_console(): void
    {
        $outputKilled = $this->getStartOutputFormatter();
        $outputKilled->expects($this->once())->method('write')->with('<killed>.</killed>');

        $dot = new DotFormatter($outputKilled);
        $dot->start(10);
        $dot->advance($this->getMutantsOfType(MutantProcess::CODE_KILLED)[0], 10);
    }

    public function test_escaped_logs_correctly_in_console(): void
    {
        $outputEscaped = $this->getStartOutputFormatter();
        $outputEscaped->expects($this->once())->method('write')->with('<escaped>M</escaped>');

        $dot = new DotFormatter($outputEscaped);
        $dot->start(10);
        $dot->advance($this->getMutantsOfType(MutantProcess::CODE_ESCAPED)[0], 10);
    }

    public function test_errored_logs_correctly_in_console(): void
    {
        $outputErrored = $this->getStartOutputFormatter();
        $outputErrored->expects($this->once())->method('write')->with('<with-error>E</with-error>');

        $dot = new DotFormatter($outputErrored);
        $dot->start(10);
        $dot->advance($this->getMutantsOfType(MutantProcess::CODE_ERROR)[0], 10);
    }

    public function test_timed_out_logs_correctly_in_console(): void
    {
        $outputTimedOut = $this->getStartOutputFormatter();
        $outputTimedOut->expects($this->once())->method('write')->with('<timeout>T</timeout>');

        $dot = new DotFormatter($outputTimedOut);
        $dot->start(10);
        $dot->advance($this->getMutantsOfType(MutantProcess::CODE_TIMED_OUT)[0], 10);
    }

    public function test_not_covered_correctly_in_console(): void
    {
        $outputNotcovered = $this->getStartOutputFormatter();
        $outputNotcovered->expects($this->once())->method('write')->with('<uncovered>S</uncovered>');

        $dot = new DotFormatter($outputNotcovered);
        $dot->start(10);
        $dot->advance($this->getMutantsOfType(MutantProcess::CODE_NOT_COVERED)[0], 10);
    }

    private function getMutantsOfType(int $mutantCode, int $count = 1): array
    {
        $mutants = [];

        for ($i = 0; $i < $count; ++$i) {
            $mutant = $this->createMock(MutantProcessInterface::class);
            $mutant->expects($this->once())->method('getResultCode')->willReturn($mutantCode);
            $mutants[] = $mutant;
        }

        return $mutants;
    }

    private function getStartOutputFormatter()
    {
        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())->method('writeln')->with([
            '',
            '<killed>.</killed>: killed, '
            . '<escaped>M</escaped>: escaped, '
            . '<uncovered>S</uncovered>: uncovered, '
            . '<with-error>E</with-error>: fatal error, '
            . '<timeout>T</timeout>: timed out',
            '',
        ]);

        return $output;
    }
}
