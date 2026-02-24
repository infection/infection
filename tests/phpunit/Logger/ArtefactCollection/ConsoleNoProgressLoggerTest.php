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
use Infection\Logger\ArtefactCollection\ConsoleNoProgressLogger;
use Infection\Logger\ArtefactCollection\InitialTestsExecution\InitialTestsExecutionLogger;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(ConsoleNoProgressLogger::class)]
final class ConsoleNoProgressLoggerTest extends TestCase
{
    private BufferedOutput $output;

    private MockObject&AbstractTestFrameworkAdapter $testFrameworkMock;

    private InitialTestsExecutionLogger $logger;

    protected function setUp(): void
    {
        $this->output = new BufferedOutput();
        $this->testFrameworkMock = $this->createMock(AbstractTestFrameworkAdapter::class);

        $this->logger = new ConsoleNoProgressLogger(
            $this->testFrameworkMock,
            $this->output,
        );
    }

    public function test_it_logs_on_start(): void
    {
        $this->testFrameworkMock
            ->expects($this->once())
            ->method('getVersion')
            ->willReturn('6.5.4');

        $this->testFrameworkMock
            ->expects($this->once())
            ->method('getName')
            ->willReturn('PHPUnit');

        $expected = <<<'EOF'

            Initial execution of PHPUnit version: 6.5.4

            EOF;

        $this->logger->start();

        $actual = Str::toUnixLineEndings($this->output->fetch());

        $this->assertSame($expected, $actual);
    }
}
