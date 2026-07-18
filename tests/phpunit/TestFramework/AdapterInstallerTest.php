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

namespace Infection\Tests\TestFramework;

use Infection\FileSystem\Finder\ComposerExecutableFinder;
use Infection\TestFramework\AdapterInstaller;
use Infection\TestFramework\Contracts\ShellCommandRunner;
use Infection\TestFramework\TestFrameworkTypes;
use Infection\Tests\TestFramework\Contracts\CompletedProcessBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdapterInstaller::class)]
final class AdapterInstallerTest extends TestCase
{
    private ComposerExecutableFinder&MockObject $composerExecutableFinder;

    private ShellCommandRunner&MockObject $shellCommandRunner;

    private AdapterInstaller $adapterInstaller;

    protected function setUp(): void
    {
        $this->composerExecutableFinder = $this->createMock(ComposerExecutableFinder::class);

        $this->shellCommandRunner = $this->createMock(ShellCommandRunner::class);

        $this->adapterInstaller = new AdapterInstaller(
            $this->composerExecutableFinder,
            $this->shellCommandRunner,
        );
    }

    #[DataProvider('adapterProvider')]
    public function test_it_installs_the_adapter(
        string $adapterName,
        string $packageName,
    ): void {
        $this->composerExecutableFinder
            ->expects($this->once())
            ->method('find')
            ->willReturn(['php', '/path/to/composer']);

        $this->shellCommandRunner
            ->expects($this->once())
            ->method('run')
            ->with(
                [
                    'php',
                    '/path/to/composer',
                    'require',
                    '--dev',
                    $packageName,
                ],
                null,
                120.0,
            )
            ->willReturn(
                CompletedProcessBuilder::withMinimalTestData()
                    ->withExitCode(1)
                    ->withStderr('Composer failed')
                    ->build(),
            );

        $this->adapterInstaller->install($adapterName);
    }

    public function test_it_rejects_an_unknown_adapter_before_running_composer(): void
    {
        $this->composerExecutableFinder
            ->expects($this->never())
            ->method('find');

        $this->shellCommandRunner
            ->expects($this->never())
            ->method('run');

        $this->expectException(InvalidArgumentException::class);

        $this->adapterInstaller->install('unknown');
    }

    public static function adapterProvider(): iterable
    {
        yield 'Codeception' => [TestFrameworkTypes::CODECEPTION, 'infection/codeception-adapter'];

        yield 'PhpSpec' => [TestFrameworkTypes::PHPSPEC, 'infection/phpspec-adapter'];

        yield 'Testo' => [TestFrameworkTypes::TESTO, 'testo/bridge-infection'];
    }
}
