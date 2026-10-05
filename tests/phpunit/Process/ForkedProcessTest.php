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

use Infection\Process\ForkedProcess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use function Safe\file_put_contents;
use function Safe\unlink;
use function sys_get_temp_dir;
use function uniqid;
use function usleep;

#[CoversClass(ForkedProcess::class)]
#[Group('integration')]
#[RequiresPhpExtension('pcntl')]
#[RequiresPhpExtension('posix')]
final class ForkedProcessTest extends TestCase
{
    public function test_it_runs_a_script_in_a_fork_of_the_worker(): void
    {
        $script = sys_get_temp_dir() . '/' . uniqid('infection-fork-', true) . '.php';
        file_put_contents($script, '<?php echo getenv("FOO"); exit(3);');

        $process = new ForkedProcess([$script], ['FOO' => 'bar'], 10.0, __DIR__ . '/../../../vendor/autoload.php', __FILE__);

        $this->assertFalse($process->isStarted());

        $process->start();

        while ($process->isRunning()) {
            usleep(1000);
        }

        unlink($script);

        $this->assertTrue($process->isTerminated());
        $this->assertSame('bar', $process->getOutput());
        $this->assertSame(3, $process->getExitCode());
    }
}
