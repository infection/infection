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

use Infection\TestFramework\Common\Process\DryRunProcess;
use Infection\TestFramework\Common\Process\DryRunProcessFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(DryRunProcessFactory::class)]
final class DryRunProcessFactoryTest extends TestCase
{
    public function test_it_creates_a_process_with_the_supplied_command_and_options(): void
    {
        $command = ['php', 'path with spaces.php'];
        $factory = new DryRunProcessFactory();
        $env = ['TEST_TOKEN' => '1', 'UNSET_ME' => false];
        $process = $factory->create($command, __DIR__, $env, 'process input', 7.5);

        $this->assertSame((new Process($command))->getCommandLine(), $process->getCommandLine());
        $this->assertSame(__DIR__, $process->getWorkingDirectory());
        $this->assertSame(['SHELL_VERBOSITY' => '0', ...$env], $process->getEnv());
        $this->assertSame('process input', $process->getInput());
        $this->assertSame(7.5, $process->getTimeout());
        $this->assertInstanceOf(DryRunProcess::class, $process);
        $this->assertTrue($process->isTerminated());
        $this->assertSame(0, $process->getExitCode());
        $this->assertSame(DryRunProcess::PASSING_TEST_OUTPUT, $process->getOutput());
    }

    public function test_it_preserves_explicit_shell_verbosity(): void
    {
        $factory = new DryRunProcessFactory();
        $process = $factory->create(['php', '-v'], null, ['SHELL_VERBOSITY' => '2'], null, null);

        $this->assertSame(['SHELL_VERBOSITY' => '2'], $process->getEnv());
    }
}
