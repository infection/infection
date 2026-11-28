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

namespace Infection\Tests\TestFramework\Common\Process;

use Infection\Process\DryRunProcess;
use Infection\TestFramework\Common\Process\DryRunAwareProcessFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(DryRunAwareProcessFactory::class)]
final class DryRunAwareProcessFactoryTest extends TestCase
{
    public function test_it_creates_an_unstarted_process(): void
    {
        $factory = new DryRunAwareProcessFactory(false);
        $process = $factory->create(['php', 'tests with spaces.php'], 20.0);

        $this->assertSame(Process::class, $process::class);
        $this->assertFalse($process->isStarted());
        $this->assertSame(['SHELL_VERBOSITY' => '0'], $process->getEnv());
        $this->assertSame(20.0, $process->getTimeout());
    }

    public function test_it_creates_an_unstarted_dry_run_process(): void
    {
        $command = ['php', 'tests with spaces.php'];
        $factory = new DryRunAwareProcessFactory(true);
        $process = $factory->create($command, 20.0);
        $expectedCommandLine = (new Process($command))->getCommandLine();

        $this->assertInstanceOf(DryRunProcess::class, $process);
        $this->assertFalse($process->isStarted());
        $this->assertSame($expectedCommandLine, $process->getCommandLine());
        $this->assertSame(['SHELL_VERBOSITY' => '0'], $process->getEnv());
        $this->assertSame(20.0, $process->getTimeout());
    }
}
