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
use Infection\Composer\ProcessBasedComposer;
use Infection\Composer\Throwable\ComposerPackageInstallationFailed;
use Infection\Composer\Throwable\IncompatibleComposerVersion;
use Infection\Composer\Throwable\UndetectableComposerVersion;
use Infection\Tests\TestingUtility\PHPUnit\ExpectsThrowables;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;
use Symfony\Component\Process\Process;

#[CoversClass(ProcessBasedComposer::class)]
final class ProcessBasedComposerTest extends TestCase
{
    use ExpectsThrowables;

    private ComposerProcessFactory&MockObject $processFactory;

    private TestLogger $logger;

    private ProcessBasedComposer $composer;

    protected function setUp(): void
    {
        $this->processFactory = $this->createMock(ComposerProcessFactory::class);
        $this->logger = new TestLogger();

        $this->composer = new ProcessBasedComposer(
            $this->processFactory,
            $this->logger,
        );
    }

    public function test_it_detects_the_composer_version(): void
    {
        $process = $this->createExecutedProcess(
            true,
            'Composer version 2.8.10 2025-01-01 12:00:00',
        );

        $this->processFactory
            ->expects($this->once())
            ->method('getVersionProcess')
            ->willReturn($process);

        $this->assertSame(
            '2.8.10',
            $this->composer->getVersion(),
        );
    }

    public function test_it_cannot_detect_the_composer_version_when_the_process_fails(): void
    {
        $process = $this->createExecutedProcess(
            false,
            'stdout',
            'stderr',
        );

        $this->processFactory
            ->expects($this->once())
            ->method('getVersionProcess')
            ->willReturn($process);

        $throwable = $this->expectToThrow($this->composer->getVersion(...));

        $this->assertInstanceOf(
            UndetectableComposerVersion::class,
            $throwable,
        );
        $this->assertSame(
            $process,
            $throwable->process,
        );
        $this->assertSame(
            [
                [
                    'level' => LogLevel::INFO,
                    'message' => 'Could not detect the Composer version.',
                    'context' => [
                        'command' => 'composer',
                        'exit_code' => 1,
                        'stdout' => 'stdout',
                        'stderr' => 'stderr',
                    ],
                ],
            ],
            $this->logger->records,
        );
    }

    public function test_it_cannot_detect_the_composer_version_when_the_output_is_not_recognized(): void
    {
        $process = $this->createExecutedProcess(
            true,
            'Composer 2.8.10',
        );

        $this->processFactory
            ->expects($this->once())
            ->method('getVersionProcess')
            ->willReturn($process);

        $throwable = $this->expectToThrow($this->composer->getVersion(...));

        $this->assertInstanceOf(
            UndetectableComposerVersion::class,
            $throwable,
        );
        $this->assertSame(
            $process,
            $throwable->process,
        );
        $this->assertSame(
            [
                [
                    'level' => LogLevel::INFO,
                    'message' => 'Could not determine the Composer version from the Composer output.',
                    'context' => [
                        'command' => 'composer',
                        'exit_code' => 0,
                        'stdout' => 'Composer 2.8.10',
                        'stderr' => '',
                    ],
                ],
            ],
            $this->logger->records,
        );
    }

    public function test_it_accepts_a_supported_composer_version(): void
    {
        $process = $this->createExecutedProcess(
            true,
            'Composer version 2.0.0 2020-10-24 12:00:00',
        );

        $this->processFactory
            ->expects($this->once())
            ->method('getVersionProcess')
            ->willReturn($process);

        $this->composer->checkVersion();
    }

    public function test_it_rejects_an_unsupported_composer_version(): void
    {
        $process = $this->createExecutedProcess(
            true,
            'Composer version 1.10.27 2023-09-29 10:50:23',
        );

        $this->processFactory
            ->expects($this->once())
            ->method('getVersionProcess')
            ->willReturn($process);

        $throwable = $this->expectToThrow($this->composer->checkVersion(...));

        $this->assertInstanceOf(
            IncompatibleComposerVersion::class,
            $throwable,
        );
        $this->assertSame(
            'The Composer version "1.10.27" does not satisfy the constraint "^2.0".',
            $throwable->getMessage(),
        );
    }

    public function test_it_gets_the_vendor_dir(): void
    {
        $process = $this->createExecutedProcess(
            true,
            "vendor\n",
        );

        $this->processFactory
            ->expects($this->once())
            ->method('getVendorDirProcess')
            ->willReturn($process);

        $this->assertSame(
            'vendor',
            $this->composer->getVendorDir(),
        );
    }

    public function test_it_returns_null_for_the_vendor_dir_when_the_process_fails(): void
    {
        $process = $this->createExecutedProcess(
            false,
            'stdout',
            'stderr',
        );

        $this->processFactory
            ->expects($this->once())
            ->method('getVendorDirProcess')
            ->willReturn($process);

        $this->assertNull($this->composer->getVendorDir());
        $this->assertSame(
            [
                [
                    'level' => LogLevel::INFO,
                    'message' => 'Could not detect the Composer vendor dir.',
                    'context' => [
                        'command' => 'composer',
                        'exit_code' => 1,
                        'stdout' => 'stdout',
                        'stderr' => 'stderr',
                    ],
                ],
            ],
            $this->logger->records,
        );
    }

    public function test_it_returns_null_for_an_empty_vendor_dir(): void
    {
        $process = $this->createExecutedProcess(
            true,
            "\n",
        );

        $this->processFactory
            ->expects($this->once())
            ->method('getVendorDirProcess')
            ->willReturn($process);

        $this->assertNull($this->composer->getVendorDir());
        $this->assertSame(
            [
                [
                    'level' => LogLevel::INFO,
                    'message' => 'Could not determine the Composer vendor dir from the Composer output.',
                    'context' => [
                        'command' => 'composer',
                        'exit_code' => 0,
                        'stdout' => "\n",
                        'stderr' => '',
                    ],
                ],
            ],
            $this->logger->records,
        );
    }

    public function test_it_gets_the_bin_dir(): void
    {
        $process = $this->createExecutedProcess(
            true,
            "vendor/bin\n",
        );

        $this->processFactory
            ->expects($this->once())
            ->method('getBinDirProcess')
            ->willReturn($process);

        $this->assertSame(
            'vendor/bin',
            $this->composer->getBinDir(),
        );
    }

    public function test_it_returns_null_for_the_bin_dir_when_the_process_fails(): void
    {
        $process = $this->createExecutedProcess(
            false,
            'stdout',
            'stderr',
        );

        $this->processFactory
            ->expects($this->once())
            ->method('getBinDirProcess')
            ->willReturn($process);

        $this->assertNull($this->composer->getBinDir());
        $this->assertSame(
            [
                [
                    'level' => LogLevel::INFO,
                    'message' => 'Could not detect the Composer bin dir.',
                    'context' => [
                        'command' => 'composer',
                        'exit_code' => 1,
                        'stdout' => 'stdout',
                        'stderr' => 'stderr',
                    ],
                ],
            ],
            $this->logger->records,
        );
    }

    public function test_it_returns_null_for_an_empty_bin_dir(): void
    {
        $process = $this->createExecutedProcess(
            true,
            "\n",
        );

        $this->processFactory
            ->expects($this->once())
            ->method('getBinDirProcess')
            ->willReturn($process);

        $this->assertNull($this->composer->getBinDir());
        $this->assertSame(
            [
                [
                    'level' => LogLevel::INFO,
                    'message' => 'Could not determine the Composer bin dir from the Composer output.',
                    'context' => [
                        'command' => 'composer',
                        'exit_code' => 0,
                        'stdout' => "\n",
                        'stderr' => '',
                    ],
                ],
            ],
            $this->logger->records,
        );
    }

    public function test_it_requires_a_dev_package(): void
    {
        $process = $this->createExecutedProcess(true);

        $this->processFactory
            ->expects($this->once())
            ->method('getRequireDevPackageProcess')
            ->with('infection/extension-installer')
            ->willReturn($process);

        $this->composer->requireDevPackage('infection/extension-installer');
    }

    public function test_it_fails_to_require_a_dev_package(): void
    {
        $process = $this->createExecutedProcess(
            false,
            'stdout',
            'stderr',
        );

        $this->processFactory
            ->expects($this->once())
            ->method('getRequireDevPackageProcess')
            ->with('infection/extension-installer')
            ->willReturn($process);

        $throwable = $this->expectToThrow(
            fn () => $this->composer->requireDevPackage('infection/extension-installer'),
        );

        $this->assertInstanceOf(
            ComposerPackageInstallationFailed::class,
            $throwable,
        );
        $this->assertSame(
            $process,
            $throwable->process,
        );
        $this->assertStringContainsString(
            'Could not install the Composer package "infection/extension-installer"',
            $throwable->getMessage(),
        );
        $this->assertSame(
            [
                [
                    'level' => LogLevel::INFO,
                    'message' => 'Could not install the Composer package.',
                    'context' => [
                        'command' => 'composer',
                        'exit_code' => 1,
                        'stdout' => 'stdout',
                        'stderr' => 'stderr',
                    ],
                ],
            ],
            $this->logger->records,
        );
    }

    private function createExecutedProcess(
        bool $successful,
        string $output = '',
        string $errorOutput = '',
    ): Process&MockObject {
        $process = $this->createMock(Process::class);

        $process
            ->expects($this->once())
            ->method('run')
            ->willReturn($successful ? 0 : 1);

        $process
            ->method('isSuccessful')
            ->willReturn($successful);

        $process
            ->method('getOutput')
            ->willReturn($output);

        $process
            ->method('getErrorOutput')
            ->willReturn($errorOutput);

        $process
            ->method('getCommandLine')
            ->willReturn('composer');

        $process
            ->method('getExitCode')
            ->willReturn($successful ? 0 : 1);

        $process
            ->method('getExitCodeText')
            ->willReturn($successful ? 'OK' : 'General error');

        $process
            ->method('getWorkingDirectory')
            ->willReturn('/path/to/project');

        $process
            ->method('isOutputDisabled')
            ->willReturn(false);

        return $process;
    }
}
