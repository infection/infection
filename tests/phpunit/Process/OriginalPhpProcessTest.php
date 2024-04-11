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

use function extension_loaded;
use Infection\Process\OriginalPhpProcess;
use function ini_get as ini_get_unsafe;
use const PHP_SAPI;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(OriginalPhpProcess::class)]
final class OriginalPhpProcessTest extends TestCase
{
    public function test_it_extends_symfony_process(): void
    {
        $process = new OriginalPhpProcess([]);

        $this->assertInstanceOf(Process::class, $process);
    }

    public function test_it_takes_command_line(): void
    {
        $process = new OriginalPhpProcess(['foo']);
        $this->assertStringContainsString('foo', $process->getCommandLine());
    }

    #[Group('integration')]
    public function test_it_injects_xdebug_env_vars(): void
    {
        $process = new OriginalPhpProcess(['env']);
        $process->run(null, ['TESTING' => 'test']);

        if (
            !extension_loaded('pcov')
            && PHP_SAPI !== 'phpdbg'
            && (
                ini_get_unsafe('xdebug.mode') === false
                || ini_get_unsafe('xdebug.mode') !== 'coverage'
            )
        ) {
            $this->assertStringContainsString('XDEBUG_MODE=coverage', $process->getOutput());
        } else {
            $this->assertStringNotContainsString('XDEBUG_MODE=coverage', $process->getOutput());
        }

        $this->assertStringContainsString('TESTING=test', $process->getOutput());
    }
}
