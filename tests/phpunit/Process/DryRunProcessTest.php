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

namespace Infection\Tests\Process;

use Infection\Process\DryRunProcess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(DryRunProcess::class)]
final class DryRunProcessTest extends TestCase
{
    public function test_it_stores_command_line_from_real_process(): void
    {
        $realProcess = new Process(['php', 'vendor/bin/phpunit', '--filter', 'SomeTest']);
        $dryRunProcess = new DryRunProcess($realProcess);

        $this->assertSame($realProcess->getCommandLine(), $dryRunProcess->getCommandLine());
    }

    public function test_it_presents_process_as_terminated(): void
    {
        $realProcess = new Process(['php', 'vendor/bin/phpunit']);
        $dryRunProcess = new DryRunProcess($realProcess);

        $this->assertTrue($dryRunProcess->isTerminated());
    }

    public function test_it_presents_process_as_started(): void
    {
        $realProcess = new Process(['php', 'vendor/bin/phpunit']);
        $dryRunProcess = new DryRunProcess($realProcess);

        $this->assertTrue($dryRunProcess->isStarted());
    }

    public function test_it_returns_passing_test_output(): void
    {
        $realProcess = new Process(['php', 'vendor/bin/phpunit']);
        $dryRunProcess = new DryRunProcess($realProcess);

        $this->assertSame(DryRunProcess::PASSING_TEST_OUTPUT, $dryRunProcess->getOutput());
    }

    public function test_it_returns_zero_start_time(): void
    {
        $realProcess = new Process(['php', 'vendor/bin/phpunit']);
        $dryRunProcess = new DryRunProcess($realProcess);

        $this->assertSame(0.0, $dryRunProcess->getStartTime());
    }

    public function test_it_returns_zero_exit_code(): void
    {
        $realProcess = new Process(['php', 'vendor/bin/phpunit']);
        $dryRunProcess = new DryRunProcess($realProcess);

        $this->assertSame(0, $dryRunProcess->getExitCode());
    }

    public function test_it_returns_terminated_status(): void
    {
        $realProcess = new Process(['php', 'vendor/bin/phpunit']);
        $dryRunProcess = new DryRunProcess($realProcess);

        $this->assertSame(Process::STATUS_TERMINATED, $dryRunProcess->getStatus());
    }

    public function test_passing_test_output_constant_triggers_escaped_status(): void
    {
        // This test documents the coupling between DryRunProcess and TestFrameworkAdapter.
        // The output must contain "OK (" to trigger testsPass() to return true.
        $this->assertStringContainsString('OK (', DryRunProcess::PASSING_TEST_OUTPUT);
    }
}
