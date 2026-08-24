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

namespace Infection\Tests\TestFramework\PhpUnit\Adapter;

use Infection\TestFramework\PhpUnit\Adapter\MemoryLimiter;
use Infection\TestFramework\PhpUnit\Adapter\MemoryLimiterEnvironment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(MemoryLimiter::class)]
final class MemoryLimiterTest extends TestCase
{
    private MockObject&MemoryLimiterEnvironment $environmentMock;

    protected function setUp(): void
    {
        $this->environmentMock = $this->createMock(MemoryLimiterEnvironment::class);
    }

    public function test_it_provides_a_memory_limit_twice_the_observed_usage(): void
    {
        $this->configureEnvironmentToBeCalledOnce();

        $memoryLimiter = new MemoryLimiter($this->environmentMock);

        $this->assertSame(['-d', 'memory_limit=40M'], $memoryLimiter->getPhpExtraArguments(20.));
    }

    public function test_it_does_nothing_when_the_adapter_cannot_detect_the_memory_used(): void
    {
        $this->configureEnvironmentToBeCalledOnce();

        $memoryLimiter = new MemoryLimiter($this->environmentMock);

        $this->assertSame([], $memoryLimiter->getPhpExtraArguments(-1.));
    }

    public function test_it_does_nothing_when_a_memory_limit_is_already_set(): void
    {
        $this->environmentMock
            ->expects($this->once())
            ->method('hasMemoryLimitSet')
            ->willReturn(true);
        $this->environmentMock
            ->expects($this->never())
            ->method('isUsingSystemIni');

        $memoryLimiter = new MemoryLimiter($this->environmentMock);

        $this->assertSame([], $memoryLimiter->getPhpExtraArguments(20.));
    }

    public function test_it_does_nothing_when_using_the_system_ini(): void
    {
        $this->environmentMock
            ->expects($this->once())
            ->method('hasMemoryLimitSet')
            ->willReturn(false);
        $this->environmentMock
            ->expects($this->once())
            ->method('isUsingSystemIni')
            ->willReturn(true);

        $memoryLimiter = new MemoryLimiter($this->environmentMock);

        $this->assertSame([], $memoryLimiter->getPhpExtraArguments(20.));
    }

    private function configureEnvironmentToBeCalledOnce(): void
    {
        $this->environmentMock
            ->expects($this->once())
            ->method('hasMemoryLimitSet')
            ->willReturn(false)
        ;

        $this->environmentMock
            ->expects($this->once())
            ->method('isUsingSystemIni')
            ->willReturn(false)
        ;
    }
}
