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

namespace Infection\Tests\Logger\Console;

use Infection\Logger\Console\BasicConsoleLogger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Stringable;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[CoversClass(BasicConsoleLogger::class)]
final class BasicConsoleLoggerTest extends TestCase
{
    /**
     * @param OutputInterface::VERBOSITY_* $outputVerbosity
     * @param LogLevel::* $level
     */
    #[DataProvider('logProvider')]
    public function test_it_can_log_messages(
        int $outputVerbosity,
        string $level,
        Stringable|string $message,
        string $expectedStdout,
        string $expectedStderr,
    ): void {
        $stderr = new BufferedOutput(verbosity: $outputVerbosity);
        $stdout = new BufferedOutputWithStdErrOutput(
            verbosity: $outputVerbosity,
            stderr: $stderr,
        );

        $logger = new BasicConsoleLogger($stdout);

        $logger->log($level, $message);

        $actualStdout = $stdout->fetch();
        $actualStderr = $stderr->fetch();

        $this->assertSame(
            [
                'stderr' => $expectedStdout,
                'stdout' => $expectedStderr,
            ],
            [
                'stderr' => $actualStdout,
                'stdout' => $actualStderr,
            ],
        );
    }

    public static function logProvider(): iterable
    {
        foreach (self::provideLogsInNormalVerbosity() as $title => $scenario) {
            yield '[verbosity=normal] ' . $title => $scenario;
        }
    }

    private static function provideLogsInNormalVerbosity(): iterable
    {
        yield 'it logs notices to the stdout' => [
            OutputInterface::VERBOSITY_NORMAL,
            LogLevel::NOTICE,
            'Hello World!',
            'Hello World!',
            '',
        ];

        yield 'it logs warnings to the stdout' => [
            OutputInterface::VERBOSITY_NORMAL,
            LogLevel::WARNING,
            'Hello World!',
            'Hello World!',
            '',
        ];

        yield 'it logs errors to the stdout' => [
            OutputInterface::VERBOSITY_NORMAL,
            LogLevel::ERROR,
            'Hello World!',
            '',
            'Hello World!',
        ];
    }
}
