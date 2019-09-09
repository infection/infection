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

namespace Infection\Tests\Console;

use Infection\Console\ConsoleOutput;
use Infection\Mutant\Exception\MsiCalculationException;
use Infection\Mutant\MetricsCalculator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ConsoleOutputTest extends TestCase
{
    public function test_log_verbosity_deprecation_notice(): void
    {
        $option = 'all';
        $io = $this->createMock(SymfonyStyle::class);
        $io->expects($this->once())->method('note')
            ->with(
                'Numeric versions of log-verbosity have been deprecated, please use, ' . $option . ' to keep the same result'
            );

        $consoleOutput = new ConsoleOutput($io);
        $consoleOutput->logVerbosityDeprecationNotice($option);
    }

    public function test_log_unknown_verbosity_option(): void
    {
        $option = 'default';
        $io = $this->createMock(SymfonyStyle::class);
        $io->expects($this->once())->method('note')
            ->with(
                'Running infection with an unknown log-verbosity option, falling back to ' . $option . ' option'
            );

        $consoleOutput = new ConsoleOutput($io);
        $consoleOutput->logUnknownVerbosityOption($option);
    }

    public function test_log_bad_msi_error_message(): void
    {
        $metrics = $this->createMock(MetricsCalculator::class);
        $metrics->expects($this->once())->method('getMutationScoreIndicator')->willReturn(75.0);
        $io = $this->createMock(SymfonyStyle::class);
        $io->expects($this->once())->method('error')->with(
            'The minimum required MSI percentage should be 25%, but actual is 75%. Improve your tests!'
        );
        $console = new ConsoleOutput($io);
        $console->logBadMsiErrorMessage($metrics, 25.0, 'min-msi');
    }

    public function test_log_bad_msi_error_message_throws_error_on_faulty_msi(): void
    {
        $io = $this->createMock(SymfonyStyle::class);
        $consoleOutput = new ConsoleOutput($io);
        $this->expectException(MsiCalculationException::class);
        $consoleOutput->logBadMsiErrorMessage(new MetricsCalculator(), 0.0, 'min-msi');
    }

    public function test_log_bad_covered_msi_error_message(): void
    {
        $metrics = $this->createMock(MetricsCalculator::class);
        $metrics->expects($this->once())->method('getCoveredCodeMutationScoreIndicator')->willReturn(75.0);
        $io = $this->createMock(SymfonyStyle::class);
        $io->expects($this->once())->method('error')->with(
            'The minimum required Covered Code MSI percentage should be 25%, but actual is 75%. Improve your tests!'
        );
        $console = new ConsoleOutput($io);
        $console->logBadMsiErrorMessage($metrics, 25.0, 'min-covered-msi');
    }

    public function test_log_running_with_debugger(): void
    {
        $io = $this->createMock(SymfonyStyle::class);
        $io->expects($this->once())->method('writeln')
            ->with('You are running Infection with foo enabled.');

        $consoleOutput = new ConsoleOutput($io);
        $consoleOutput->logRunningWithDebugger('foo');
    }

    public function test_log_not_in_control_of_exit_codes(): void
    {
        $io = $this->createMock(SymfonyStyle::class);
        $io->expects($this->once())->method('warning')
            ->with([
                'Infection cannot control exit codes and unable to relaunch itself.' . PHP_EOL .
                'It is your responsibility to disable xdebug/phpdbg unless needed.',
            ]);

        $consoleOutput = new ConsoleOutput($io);
        $consoleOutput->logNotInControlOfExitCodes();
    }
}
