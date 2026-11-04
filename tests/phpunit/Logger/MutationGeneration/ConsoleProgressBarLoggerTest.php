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

namespace Infection\Tests\Logger\MutationGeneration;

use Infection\Console\IO;
use Infection\Logger\Console\ConsoleLogger;
use Infection\Logger\MutationGeneration\ConsoleProgressBarLogger;
use Infection\Logger\MutationGeneration\PsrMutationGenerationDiagnosticLogger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[CoversClass(ConsoleProgressBarLogger::class)]
final class ConsoleProgressBarLoggerTest extends TestCase
{
    public function test_it_logs_the_start(): void
    {
        $outputMock = $this->createMock(OutputInterface::class);

        $logger = new ConsoleProgressBarLogger(
            $outputMock,
            new PsrMutationGenerationDiagnosticLogger(
                new NullLogger(),
                true,
            ),
        );

        $outputMock
            ->expects($this->once())
            ->method('getVerbosity')
            ->willReturn(OutputInterface::VERBOSITY_QUIET)
        ;
        $outputMock
            ->expects($this->once())
            ->method('writeln')
            ->with([
                '',
                'Generate mutants...',
                '',
            ])
        ;

        $logger->start(10);
    }

    #[DataProvider('provideDiagnostics')]
    public function test_it_flushes_diagnostics_after_finishing_the_progress_bar(bool $withDiagnostics): void
    {
        $output = new BufferedOutput();
        $diagnosticLogger = new PsrMutationGenerationDiagnosticLogger(
            new ConsoleLogger(
                new IO(
                    new StringInput(''),
                    $output,
                ),
            ),
            true,
        );

        $logger = new ConsoleProgressBarLogger(
            $output,
            $diagnosticLogger,
        );

        $logger->start(1);
        $output->fetch();

        if ($withDiagnostics) {
            $diagnosticLogger->warning('Unknown mutator "UnknownMutator".');
            $diagnosticLogger->warning('No mutator names.');
        }

        $this->assertSame('', $output->fetch());

        $logger->advance();
        $logger->finish();

        $expected = $withDiagnostics
            ? "\nProcessing source code files: 1/1\n[warning] Unknown mutator \"UnknownMutator\".\n[warning] No mutator names.\n"
            : "\nProcessing source code files: 1/1";

        $this->assertSame($expected, $output->fetch());
    }

    public static function provideDiagnostics(): iterable
    {
        yield 'buffered diagnostics appear below the finished progress bar' => [true];

        yield 'an empty buffer adds no newline' => [false];
    }
}
