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
use Infection\Console\LogVerbosity;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

final class LogVerbosityTest extends TestCase
{
    /**
     * @var InputInterface|MockObject
     */
    private $inputMock;

    /**
     * @var ConsoleOutput|MockObject
     */
    private $consoleOutputMock;

    protected function setUp(): void
    {
        $this->inputMock = $this->createMock(InputInterface::class);
        $this->consoleOutputMock = $this->createMock(ConsoleOutput::class);
    }

    public function test_it_works_if_verbosity_is_valid(): void
    {
        $this->setInputExpectationsWhenItDoesNotChange(LogVerbosity::NORMAL);

        $this->consoleOutputMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        LogVerbosity::convertVerbosityLevel($this->inputMock, $this->consoleOutputMock);
    }

    /**
     * @dataProvider convertedLogVerbosityProvider
     */
    public function test_it_converts_int_version_to_string_version_of_verbosity(
        $inputVerbosity,
        string $output
    ): void {
        $this->setInputExpectationsWhenItDoesChange($inputVerbosity, $output);

        $this->consoleOutputMock
            ->expects($this->once())
            ->method('logVerbosityDeprecationNotice')
            ->with($output)
        ;

        LogVerbosity::convertVerbosityLevel($this->inputMock, $this->consoleOutputMock);
    }

    public function test_it_converts_to_normal_and_writes_notice_when_invalid_verbosity(): void
    {
        $this->setInputExpectationsWhenItDoesChange('asdf', LogVerbosity::NORMAL);

        $this->consoleOutputMock
            ->expects($this->once())
            ->method('logUnknownVerbosityOption')
            ->with('default')
        ;

        LogVerbosity::convertVerbosityLevel($this->inputMock, $this->consoleOutputMock);
    }

    public static function convertedLogVerbosityProvider(): iterable
    {
        yield 'none integer to none' => [
            LogVerbosity::NONE_INTEGER,
            LogVerbosity::NONE,
        ];

        yield 'normal integer to normal' => [
            LogVerbosity::NORMAL_INTEGER,
            LogVerbosity::NORMAL,
        ];

        yield 'debug integer to debug' => [
            LogVerbosity::DEBUG_INTEGER,
            LogVerbosity::DEBUG,
        ];

        yield 'string version of debug integer to debug' => [
            (string) LogVerbosity::DEBUG_INTEGER,
            LogVerbosity::DEBUG,
        ];
    }

    /**
     * @param string|int $inputVerbosity
     */
    private function setInputExpectationsWhenItDoesNotChange($inputVerbosity): void
    {
        $this->inputMock
            ->expects($this->once())
            ->method('getOption')
            ->with('log-verbosity')
            ->willReturn($inputVerbosity)
        ;
    }

    /**
     * @param string|int $inputVerbosity
     */
    private function setInputExpectationsWhenItDoesChange($inputVerbosity, string $output): void
    {
        $this->setInputExpectationsWhenItDoesNotChange($inputVerbosity);

        $this->inputMock
            ->expects($this->once())
            ->method('setOption')
            ->with('log-verbosity', $output)
        ;
    }
}
