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

namespace Infection\Tests\Process\Factory;

use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Process\Factory\InitialTestsRunProcessFactory;
use Infection\Process\XdebugProcess;
use const PHP_OS_FAMILY;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class InitialTestsRunProcessFactoryTest extends TestCase
{
    /**
     * @var TestFrameworkAdapter|MockObject
     */
    private $testFrameworkAdapterMock;

    /**
     * @var InitialTestsRunProcessFactory
     */
    private $factory;

    protected function setUp(): void
    {
        $this->testFrameworkAdapterMock = $this->createMock(TestFrameworkAdapter::class);

        $this->factory = new InitialTestsRunProcessFactory($this->testFrameworkAdapterMock);
    }

    public function test_it_creates_a_process_with_coverage_skipped(): void
    {
        $testFrameworkExtraOptions = '--stop-on-failure';
        $phpExtraOptions = ['-d memory_limit=-1'];

        $this->testFrameworkAdapterMock
            ->method('getInitialTestRunCommandLine')
            ->with($testFrameworkExtraOptions, $phpExtraOptions, true)
            ->willReturn(['/usr/bin/php'])
        ;

        $process = $this->factory->createProcess(
            $testFrameworkExtraOptions,
            $phpExtraOptions,
            true
        );

        if (PHP_OS_FAMILY === 'Windows') {
            $this->assertSame('"/usr/bin/php"', $process->getCommandLine());
        } else {
            $this->assertSame('\'/usr/bin/php\'', $process->getCommandLine());
        }

        $this->assertNull($process->getTimeout());
        $this->assertNotInstanceOf(XdebugProcess::class, $process);
    }

    public function test_it_creates_a_process_with_coverage(): void
    {
        $testFrameworkExtraOptions = '--stop-on-failure';
        $phpExtraOptions = ['-d memory_limit=-1'];

        $this->testFrameworkAdapterMock
            ->method('getInitialTestRunCommandLine')
            ->with($testFrameworkExtraOptions, $phpExtraOptions, false)
            ->willReturn(['/usr/bin/php'])
        ;

        $process = $this->factory->createProcess(
            $testFrameworkExtraOptions,
            $phpExtraOptions,
            false
        );

        if (PHP_OS_FAMILY === 'Windows') {
            $this->assertSame('"/usr/bin/php"', $process->getCommandLine());
        } else {
            $this->assertSame('\'/usr/bin/php\'', $process->getCommandLine());
        }

        $this->assertNull($process->getTimeout());
        $this->assertInstanceOf(XdebugProcess::class, $process);
    }
}
