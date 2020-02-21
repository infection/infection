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
use const PHP_EOL;
use PHPUnit\Framework\MockObject\MockObject;
use function str_replace;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group integration Requires some I/O operations
 */
final class SummaryFileLoggerTest extends FileSystemTestCase
{
    private const LOG_FILE_PATH = '/path/to/text.log';

    /**
     * @var Filesystem|MockObject
     */
    private $fileSystemMock;

    /**
     * @var OutputInterface|MockObject
     */
    private $outputMock;

    protected function setUp(): void
    {
        $this->fileSystemMock = $this->createMock(Filesystem::class);
        $this->outputMock = $this->createMock(OutputInterface::class);
    }

    public function test_it_logs_the_correct_lines_with_no_mutations(): void
    {
        $expectedContent = <<<'TXT'
Total: 0
Killed: 0
Errored: 0
Escaped: 0
Timed Out: 0
TXT;

        $expectedContent = str_replace("\n", PHP_EOL, $expectedContent);

        $this->fileSystemMock
            ->expects($this->once())
            ->method('dumpFile')
            ->with(self::LOG_FILE_PATH, $expectedContent)
        ;

        $debugFileLogger = new SummaryFileLogger(
            $this->outputMock,
            self::LOG_FILE_PATH,
            new MetricsCalculator(),
            $this->fileSystemMock,
            false,
            false
        );

        $debugFileLogger->log();
    }

    public function test_it_logs_the_correct_lines_with_mutations(): void
    {
        $calculatorMock = $this->createMock(MetricsCalculator::class);
        $calculatorMock
            ->expects($this->once())
            ->method('getTotalMutantsCount')
            ->willReturn(6)
        ;
        $calculatorMock
            ->expects($this->once())
            ->method('getKilledCount')
            ->willReturn(8)
        ;
        $calculatorMock
            ->expects($this->once())
            ->method('getErrorCount')
            ->willReturn(7)
        ;
        $calculatorMock
            ->expects($this->once())
            ->method('getEscapedCount')
            ->willReturn(30216)
        ;
        $calculatorMock
            ->expects($this->once())
            ->method('getTimedOutCount')
            ->willReturn(2)
        ;

        $expectedContent = <<<'TXT'
Total: 6
Killed: 8
Errored: 7
Escaped: 30216
Timed Out: 2
TXT;

        $expectedContent = str_replace("\n", PHP_EOL, $expectedContent);

        $this->fileSystemMock
            ->expects($this->once())
            ->method('dumpFile')
            ->with(self::LOG_FILE_PATH, $expectedContent)
        ;

        $debugFileLogger = new SummaryFileLogger(
            $this->outputMock,
            self::LOG_FILE_PATH,
            $calculatorMock,
            $this->fileSystemMock,
            false,
            false
        );

        $debugFileLogger->log();
    }

    public function test_it_cannot_log_on_invalid_streams(): void
    {
        $this->outputMock
            ->expects($this->once())
            ->method('writeln')
            ->with('<error>The only streams supported are php://stdout and php://stderr</error>')
        ;

        $debugFileLogger = new SummaryFileLogger(
            $this->outputMock,
            'php://memory',
            new MetricsCalculator(),
            $this->fileSystemMock,
            false,
            false
        );

        $debugFileLogger->log();
    }

    public function test_it_fails_if_cannot_write_file(): void
    {
        $this->fileSystemMock
            ->expects($this->once())
            ->method('dumpFile')
            ->with(self::LOG_FILE_PATH, $this->anything())
            ->willThrowException(new IOException('Cannot write in directory X'));

        $this->outputMock
            ->expects($this->once())
            ->method('writeln')
            ->with('<error>Cannot write in directory X</error>')
        ;

        $debugFileLogger = new SummaryFileLogger(
            $this->outputMock,
            self::LOG_FILE_PATH,
            new MetricsCalculator(),
            $this->fileSystemMock,
            false,
            false
        );

        $debugFileLogger->log();
    }
}
