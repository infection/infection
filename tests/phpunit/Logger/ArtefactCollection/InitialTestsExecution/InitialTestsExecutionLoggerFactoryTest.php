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

namespace Infection\Tests\Logger\ArtefactCollection\InitialTestsExecution;

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Logger\ArtefactCollection\ConsoleNoProgressLogger;
use Infection\Logger\ArtefactCollection\ConsoleProgressBarLogger;
use Infection\Logger\ArtefactCollection\InitialTestsExecution\InitialTestsExecutionLoggerFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

#[CoversClass(InitialTestsExecutionLoggerFactory::class)]
final class InitialTestsExecutionLoggerFactoryTest extends TestCase
{
    private TestFrameworkAdapter&MockObject $testFrameworkAdapterMock;

    private OutputInterface&MockObject $outputMock;

    protected function setUp(): void
    {
        $this->testFrameworkAdapterMock = $this->createMock(TestFrameworkAdapter::class);
        $this->testFrameworkAdapterMock
            ->expects($this->never())
            ->method($this->anything());

        $this->outputMock = $this->createMock(OutputInterface::class);
        // We don't explicitly rely on this; this is only necessary for the
        // ProgressBar used internally.
        $this->outputMock
            ->method('isDecorated')
            ->willReturn(false);
    }

    #[DataProvider('debugProvider')]
    public function test_it_creates_a_no_progress_logger_if_skips_the_progress_bar(bool $debug): void
    {
        $factory = $this->createFactory(
            skipProgressBar: true,
            debug: $debug,
        );

        $logger = $factory->create();

        $this->assertInstanceOf(ConsoleNoProgressLogger::class, $logger);
    }

    #[DataProvider('debugProvider')]
    public function test_it_creates_a_progress_bar_logger_if_does_not_skip_the_progress_bar(bool $debug): void
    {
        $factory = $this->createFactory(
            skipProgressBar: false,
            debug: $debug,
        );

        $logger = $factory->create();

        $this->assertInstanceOf(ConsoleProgressBarLogger::class, $logger);
    }

    public static function debugProvider(): iterable
    {
        yield 'debug enabled' => [true];

        yield 'debug disabled' => [false];
    }

    private function createFactory(
        bool $skipProgressBar,
        bool $debug,
    ): InitialTestsExecutionLoggerFactory {
        return new InitialTestsExecutionLoggerFactory(
            $skipProgressBar,
            $this->testFrameworkAdapterMock,
            $debug,
            $this->outputMock,
        );
    }
}
