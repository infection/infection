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

namespace Infection\Tests\Logger\ArtefactCollection;

use Infection\Logger\ArtefactCollection\ConsoleProgressBarLogger;
use Infection\Logger\ArtefactCollection\InitialTestsExecution\InitialTestsExecutionLogger;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(ConsoleProgressBarLogger::class)]
final class ConsoleProgressBarLoggerTest extends TestCase
{
    private OutputInterface&MockObject $outputMock;

    protected function setUp(): void
    {
        $this->outputMock = $this->createMock(OutputInterface::class);
    }

    public function test_it_logs_the_start(): void
    {
        $this->outputMock
            ->expects($this->once())
            ->method('writeln')
            ->with([
                '',
                'Running initial tests with PHPUnit version 12.0.0',
                '',
            ]);
        $this->outputMock
            ->method('getVerbosity')
            ->willReturn(OutputInterface::VERBOSITY_QUIET);

        $this->createLogger(debug: false)->start('PHPUnit', '12.0.0');
    }

    public function test_it_does_not_output_the_initial_process_text_if_in_debug_mode_on_finish(): void
    {
        $testOutput = 'PHPUnit Test suite ...';

        $this->outputMock
            ->expects($this->once())
            ->method('writeln')
            ->with('');

        $this->outputMock
            ->method('getVerbosity')
            ->willReturn(OutputInterface::VERBOSITY_QUIET);

        $this->createLogger(debug: false)->finish($testOutput);
    }

    public function test_it_outputs_the_initial_process_text_if_in_debug_mode_on_finish(): void
    {
        $testOutput = 'PHPUnit Test suite ...';

        $this->outputMock
            ->expects($this->exactly(2))
            ->method('writeln');

        $this->outputMock
            ->method('getVerbosity')
            ->willReturn(OutputInterface::VERBOSITY_QUIET);

        $this->createLogger(debug: true)->finish($testOutput);
    }

    private function createLogger(bool $debug): InitialTestsExecutionLogger
    {
        return new ConsoleProgressBarLogger(
            $this->outputMock,
            $debug,
        );
    }
}
