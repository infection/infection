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

namespace Infection\Tests\TestFramework\Common;

use Infection\Process\OriginalPhpProcess;
use Infection\TestFramework\Common\InitialRunProcessFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

#[CoversClass(InitialRunProcessFactory::class)]
final class InitialRunProcessFactoryTest extends TestCase
{
    /**
     * @param class-string<Process> $expectedProcessClass
     */
    #[DataProvider('phpConfigurationProvider')]
    public function test_it_preserves_the_command_environment_and_timeout(
        bool $useOriginalPhpConfig,
        string $expectedProcessClass,
    ): void {
        $command = ['php', '/path with spaces/test.php', '--filter', 'test with spaces'];
        $factory = new InitialRunProcessFactory();
        $process = $factory->create($command, $useOriginalPhpConfig);
        $expectedCommandLine = (new Process($command))->getCommandLine();
        $actualProcessClass = $process::class;

        $this->assertSame($expectedProcessClass, $actualProcessClass);
        $this->assertSame($expectedCommandLine, $process->getCommandLine());
        $this->assertSame(['SHELL_VERBOSITY' => '0'], $process->getEnv());
        $this->assertNull($process->getTimeout());
    }

    public static function phpConfigurationProvider(): iterable
    {
        yield 'regular PHP configuration' => [false, Process::class];

        yield 'original PHP configuration for coverage' => [true, OriginalPhpProcess::class];
    }
}
