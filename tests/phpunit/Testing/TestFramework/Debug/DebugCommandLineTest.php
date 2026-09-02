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

namespace Infection\Tests\Testing\TestFramework\Debug;

use function base64_encode;
use Infection\FileSystem\Finder\Exception\FinderException;
use Infection\Testing\TestFramework\Debug\DebugCommandLine;
use function json_encode;
use const JSON_THROW_ON_ERROR;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\PhpExecutableFinder;

#[CoversClass(DebugCommandLine::class)]
final class DebugCommandLineTest extends TestCase
{
    private DebugCommandLine $commandLine;

    protected function setUp(): void
    {
        $phpExecutableFinder = $this->createStub(PhpExecutableFinder::class);
        $phpExecutableFinder
            ->method('find')
            ->willReturn('/php')
        ;

        $this->commandLine = new DebugCommandLine($phpExecutableFinder);
    }

    /**
     * @param string[] $phpArguments
     * @param array<string, string> $options
     * @param string[] $expected
     */
    #[DataProvider('commandLineProvider')]
    public function test_it_builds_a_self_describing_command(
        string $runtime,
        array $phpArguments,
        array $options,
        array $expected,
    ): void {
        $actual = $this->commandLine->create($runtime, $phpArguments, $options);

        $this->assertSame($expected, $actual);
    }

    public static function commandLineProvider(): iterable
    {
        yield 'filesystem runtime with PHP arguments and options' => [
            'runtime' => '/debug.php',
            'phpArguments' => ['', '-d', 'memory_limit=-1'],
            'options' => [
                'stage' => 'initial',
                'log' => '/tmp/infection',
            ],
            'expected' => self::createExpectedCommand([
                '/php',
                '-d',
                'memory_limit=-1',
                '/debug.php',
                '--stage',
                'initial',
                '--log',
                '/tmp/infection',
            ]),
        ];

        yield 'PHAR runtime without optional arguments' => [
            'runtime' => 'phar:///infection.phar/debug.php',
            'phpArguments' => [],
            'options' => [],
            'expected' => self::createExpectedCommand([
                '/php',
                '-r',
                "require 'phar:///infection.phar/debug.php';",
                '--',
            ]),
        ];
    }

    public function test_it_memoizes_the_php_executable(): void
    {
        $phpExecutableFinder = $this->createMock(PhpExecutableFinder::class);
        $phpExecutableFinder
            ->expects($this->once())
            ->method('find')
            ->with(false)
            ->willReturn('/php')
        ;

        $commandLine = new DebugCommandLine($phpExecutableFinder);

        $commandLine->create('/debug.php', [], []);
        $commandLine->create('/debug.php', [], []);
    }

    public function test_it_cannot_build_a_command_without_a_php_executable(): void
    {
        $phpExecutableFinder = $this->createStub(PhpExecutableFinder::class);
        $phpExecutableFinder
            ->method('find')
            ->willReturn(false)
        ;

        $commandLine = new DebugCommandLine($phpExecutableFinder);

        $this->expectExceptionObject(FinderException::phpExecutableNotFound());

        $commandLine->create('/debug.php', [], []);
    }

    /**
     * @param string[] $commandLine
     *
     * @return string[]
     */
    private static function createExpectedCommand(array $commandLine): array
    {
        return [
            ...$commandLine,
            '--command',
            base64_encode(json_encode($commandLine, JSON_THROW_ON_ERROR)),
        ];
    }
}
