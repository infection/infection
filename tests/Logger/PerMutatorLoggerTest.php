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

use Infection\Logger\PerMutatorLogger;
use Infection\Mutant\MetricsCalculator;
use Infection\Mutator\Regex\PregQuote;
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
final class PerMutatorLoggerTest extends TestCase
{
    public function test_it_correctly_build_log_lines(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $fs = $this->createMock(Filesystem::class);
        $fs->expects($this->once())
            ->method('dumpFile')
            ->with(
                sys_get_temp_dir() . '/fake-file.md',
                "# Effects per Mutator\n" .
                "\n" .
                "| Mutator | Mutations | Killed | Escaped | Errors | Timed Out | MSI | Covered MSI |\n" .
                "| ------- | --------- | ------ | ------- |------- | --------- | --- | ----------- |\n" .
                "| For_ | 15 | 10 | 0 | 0 | 0 | 66| 100|\n" .
                '| PregQuote | 5 | 0 | 0 | 0 | 0 | 0| 0|'
            );

        $perMutatorLogger = new PerMutatorLogger(
            $output,
            sys_get_temp_dir() . '/fake-file.md',
            $this->createMetricsCalculator(),
            $fs,
            true,
            true
        );

        $perMutatorLogger->log();
    }

    private function createMetricsCalculator(): MetricsCalculator
    {
        $processes = [];

        for ($i = 0; $i < 10; ++$i) {
            $mutantFor = $this->createMock(MutantProcessInterface::class);
            $mutantFor->expects($this->once())->method('getMutator')->willReturn(new For_(new MutatorConfig([])));
            $mutantFor->expects($this->exactly(2))->method('getResultCode')->willReturn(MutantProcess::CODE_KILLED);
            $processes[] = $mutantFor;
        }

        for ($i = 0; $i < 5; ++$i) {
            $mutantFor = $this->createMock(MutantProcessInterface::class);
            $mutantFor->expects($this->once())->method('getMutator')->willReturn(new For_(new MutatorConfig([])));
            $mutantFor->expects($this->exactly(2))->method('getResultCode')->willReturn(MutantProcess::CODE_NOT_COVERED);
            $processes[] = $mutantFor;
        }

        for ($i = 0; $i < 5; ++$i) {
            $mutantFor = $this->createMock(MutantProcessInterface::class);
            $mutantFor->expects($this->once())->method('getMutator')->willReturn(new PregQuote(new MutatorConfig([])));
            $mutantFor->expects($this->exactly(2))->method('getResultCode')->willReturn(MutantProcess::CODE_NOT_COVERED);
            $processes[] = $mutantFor;
        }

        return MetricsCalculator::createFromArray($processes);
    }
}
