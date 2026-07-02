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

namespace Infection\Tests\Composer;

use Infection\Composer\ComposerProcessFactory;
use Infection\FileSystem\Finder\ComposerExecutableFinder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComposerProcessFactory::class)]
final class ComposerProcessFactoryTest extends TestCase
{
    private ComposerExecutableFinder&MockObject $composerExecutableFinder;

    private ComposerProcessFactory $factory;

    protected function setUp(): void
    {
        $this->composerExecutableFinder = $this->createMock(ComposerExecutableFinder::class);

        $this->factory = new ComposerProcessFactory($this->composerExecutableFinder);
    }

    public function test_it_creates_a_process_to_get_the_composer_version(): void
    {
        $this->composerExecutableFinder
            ->expects($this->once())
            ->method('find')
            ->willReturn([
                '/usr/bin/php',
                '/path/to/composer.phar',
            ]);

        $process = $this->factory->getVersionProcess();

        $this->assertStringContainsString(
            '/usr/bin/php',
            $process->getCommandLine(),
        );
        $this->assertStringContainsString(
            '/path/to/composer.phar',
            $process->getCommandLine(),
        );
        $this->assertStringContainsString(
            '--version',
            $process->getCommandLine(),
        );
        $this->assertStringContainsString(
            '--no-ansi',
            $process->getCommandLine(),
        );
        $this->assertSame(
            0,
            $process->getEnv()['SHELL_VERBOSITY'],
        );
    }

    public function test_it_creates_a_process_to_get_the_vendor_dir(): void
    {
        $this->composerExecutableFinder
            ->expects($this->once())
            ->method('find')
            ->willReturn(['/usr/bin/composer']);

        $process = $this->factory->getVendorDirProcess();

        $this->assertStringContainsString(
            '/usr/bin/composer',
            $process->getCommandLine(),
        );
        $this->assertStringContainsString(
            'config',
            $process->getCommandLine(),
        );
        $this->assertStringContainsString(
            'vendor-dir',
            $process->getCommandLine(),
        );
        $this->assertStringContainsString(
            '--no-ansi',
            $process->getCommandLine(),
        );
        $this->assertSame(
            0,
            $process->getEnv()['SHELL_VERBOSITY'],
        );
    }

    public function test_it_creates_a_process_to_get_the_bin_dir(): void
    {
        $this->composerExecutableFinder
            ->expects($this->once())
            ->method('find')
            ->willReturn(['/usr/bin/composer']);

        $process = $this->factory->getBinDirProcess();

        $this->assertStringContainsString(
            '/usr/bin/composer',
            $process->getCommandLine(),
        );
        $this->assertStringContainsString(
            'config',
            $process->getCommandLine(),
        );
        $this->assertStringContainsString(
            'bin-dir',
            $process->getCommandLine(),
        );
        $this->assertStringContainsString(
            '--no-ansi',
            $process->getCommandLine(),
        );
        $this->assertSame(
            0,
            $process->getEnv()['SHELL_VERBOSITY'],
        );
    }

    public function test_it_creates_a_process_to_require_a_dev_package(): void
    {
        $this->composerExecutableFinder
            ->expects($this->once())
            ->method('find')
            ->willReturn(['/usr/bin/composer']);

        $process = $this->factory->getRequireDevPackageProcess(
            'infection/extension-installer',
        );

        $this->assertStringContainsString(
            '/usr/bin/composer',
            $process->getCommandLine(),
        );
        $this->assertStringContainsString(
            'require',
            $process->getCommandLine(),
        );
        $this->assertStringContainsString(
            '--dev',
            $process->getCommandLine(),
        );
        $this->assertStringContainsString(
            'infection/extension-installer',
            $process->getCommandLine(),
        );
        $this->assertSame(
            120.0,
            $process->getTimeout(),
        );
    }

    public function test_it_memoizes_the_composer_executable(): void
    {
        $this->composerExecutableFinder
            ->expects($this->once())
            ->method('find')
            ->willReturn(['/usr/bin/composer']);

        $this->factory->getVersionProcess();
        $this->factory->getVendorDirProcess();
        $this->factory->getBinDirProcess();
        $this->factory->getRequireDevPackageProcess(
            'infection/extension-installer',
        );
    }
}
