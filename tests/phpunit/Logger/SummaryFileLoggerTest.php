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

use Infection\Logger\SummaryFileLogger;
use Infection\Mutant\MetricsCalculator;
use Infection\Tests\FileSystem\FileSystemTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class SummaryFileLoggerTest extends FileSystemTestCase
{
    public function test_it_logs_the_correct_lines_with_no_mutations(): void
    {
        $logFilePath = $this->tmp . '/foo.txt';
        $calculator = new MetricsCalculator();
        $output = $this->createMock(OutputInterface::class);
        $content = <<<'TXT'
Total: 0
Killed: 0
Errored: 0
Escaped: 0
Timed Out: 0
Not Covered: 0
TXT;
        $content = str_replace("\n", PHP_EOL, $content);

        $fs = $this->createMock(Filesystem::class);
        $fs->expects($this->once())->method('dumpFile')->with(
            $logFilePath,
            $content
        );

        $debugFileLogger = new SummaryFileLogger($output, $logFilePath, $calculator, $fs, false, false);
        $debugFileLogger->log();
    }

    public function test_it_logs_the_correct_lines_with_mutations(): void
    {
        $logFilePath = $this->tmp . '/foo.txt';
        $calculator = $this->createMock(MetricsCalculator::class);
        $calculator->expects($this->once())->method('getTotalMutantsCount')->willReturn(6);
        $calculator->expects($this->once())->method('getKilledCount')->willReturn(8);
        $calculator->expects($this->once())->method('getErrorCount')->willReturn(7);
        $calculator->expects($this->once())->method('getEscapedCount')->willReturn(30216);
        $calculator->expects($this->once())->method('getTimedOutCount')->willReturn(2);
        $calculator->expects($this->once())->method('getNotCoveredByTestsCount')->willReturn(0);
        $output = $this->createMock(OutputInterface::class);
        $content = <<<'TXT'
Total: 6
Killed: 8
Errored: 7
Escaped: 30216
Timed Out: 2
Not Covered: 0
TXT;
        $content = str_replace("\n", PHP_EOL, $content);

        $fs = $this->createMock(Filesystem::class);
        $fs->expects($this->once())->method('dumpFile')->with(
            $logFilePath,
            $content
        );

        $debugFileLogger = new SummaryFileLogger($output, $logFilePath, $calculator, $fs, false, false);
        $debugFileLogger->log();
    }

    /**
     * @requires OSFAMILY Windows Cannot test file permission on Windows
     */
    public function test_it_outputs_an_error_when_dir_is_not_writable(): void
    {
        $readOnlyDirPath = $this->tmp . '/invalid';
        $logFilePath = $readOnlyDirPath . '/foo.txt';

        // make it readonly
        (new Filesystem())->mkdir($readOnlyDirPath, 0400);

        if (is_writable($readOnlyDirPath)) {
            $this->markTestSkipped('Unable to change file permission to 0400');
        }

        $calculator = $this->createMock(MetricsCalculator::class);
        $calculator->expects($this->once())->method('getTotalMutantsCount')->willReturn(6);
        $calculator->expects($this->once())->method('getKilledCount')->willReturn(8);
        $calculator->expects($this->once())->method('getErrorCount')->willReturn(7);
        $calculator->expects($this->once())->method('getEscapedCount')->willReturn(30216);
        $calculator->expects($this->once())->method('getTimedOutCount')->willReturn(2);
        $calculator->expects($this->once())->method('getNotCoveredByTestsCount')->willReturn(0);

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())->method('writeln')->with(
            sprintf(
                '<error>Unable to write to the "%s" directory.</error>',
                $readOnlyDirPath
            )
        );

        $fs = new Filesystem();

        $debugFileLogger = new SummaryFileLogger($output, $logFilePath, $calculator, $fs, false, false);
        $debugFileLogger->log();
    }
}
