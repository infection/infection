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

use Infection\Framework\Str;
use Infection\Logger\ArtefactCollection\ConsoleProgressBarLogger;
use Infection\Logger\ArtefactCollection\InitialTestsExecution\InitialTestsExecutionLogger;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
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
                'Running initial tests with PHPUnit version 6.5.4',
                '',
            ])
        ;
        $this->outputMock
            ->method('getVerbosity')
            ->willReturn(OutputInterface::VERBOSITY_QUIET)
        ;

        $this->createLogger(debug: false)->start('PHPUnit', '6.5.4');
    }

    public function test_it_displays_progress_when_a_framework_starts(): void
    {
        $output = new BufferedOutput(decorated: true);
        $logger = new ConsoleProgressBarLogger($output, false);

        $logger->start('PHPUnit', '6.5.4');

        $this->assertMatchesRegularExpression('/0 \[.*\]\s+\S/', $output->fetch());
    }

    #[DataProvider('debugProvider')]
    public function test_it_logs_the_finished_run(bool $debug, string $expectedOutput): void
    {
        $output = new BufferedOutput();
        $logger = new ConsoleProgressBarLogger($output, $debug);
        $logger->start('PHPUnit', '6.5.4');
        $output->fetch();

        $logger->finish('PHPUnit', 'PHPUnit Test suite ...');

        $actualOutput = Str::toUnixLineEndings($output->fetch());

        $this->assertSame($expectedOutput, $actualOutput);
    }

    public function test_it_tracks_progress_separately_for_each_framework(): void
    {
        $output = new BufferedOutput(decorated: true);
        $logger = new ConsoleProgressBarLogger($output, false);
        $logger->start('PHPUnit', '6.5.4');
        $logger->advance('PHPUnit');
        $logger->start('Pest', '3.0.0');
        $logger->advance('Pest');
        $logger->advance('PHPUnit');
        $output->fetch();

        $logger->finish('PHPUnit', 'PHPUnit output');
        $phpunitOutput = $output->fetch();
        $logger->finish('Pest', 'Pest output');
        $pestOutput = $output->fetch();

        $this->assertMatchesRegularExpression('/2 \[.*\]\s+\S/', $phpunitOutput);
        $this->assertMatchesRegularExpression('/1 \[.*\]\s+\S/', $pestOutput);
        $this->assertStringNotContainsString('PHPUnit output', $phpunitOutput);
        $this->assertStringNotContainsString('Pest output', $pestOutput);
    }

    public function test_it_starts_a_fresh_progress_bar_for_a_repeated_framework(): void
    {
        $output = new BufferedOutput(decorated: true);
        $logger = new ConsoleProgressBarLogger($output, false);
        $logger->start('PHPUnit', '6.5.4');
        $logger->advance('PHPUnit');
        $logger->advance('PHPUnit');
        $logger->finish('PHPUnit', 'First run');
        $output->fetch();

        $logger->start('PHPUnit', '6.5.4');
        $logger->advance('PHPUnit');
        $output->fetch();
        $logger->finish('PHPUnit', 'Second run');

        $this->assertMatchesRegularExpression('/1 \[.*\]\s+\S/', $output->fetch());
    }

    public static function debugProvider(): iterable
    {
        yield 'debug disabled' => [false, "\n"];

        yield 'debug enabled' => [true, "\n\nPHPUnit Test suite ...\n"];
    }

    private function createLogger(bool $debug): InitialTestsExecutionLogger
    {
        return new ConsoleProgressBarLogger($this->outputMock, $debug);
    }
}
