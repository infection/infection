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

namespace Infection\Tests\Configuration\ProjectDirectoryProvider;

use Exception;
use Infection\Configuration\ProjectDirectoryProvider\EnvironmentVariableBasedProjectDirectoryProvider;
use Infection\FileSystem\FileSystem;
use Infection\Tests\EnvVariableManipulation\BacksUpEnvironmentVariables;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Safe\putenv;

#[CoversClass(EnvironmentVariableBasedProjectDirectoryProvider::class)]
final class EnvironmentVariableBasedProjectDirectoryProviderTest extends TestCase
{
    use BacksUpEnvironmentVariables;

    protected function setUp(): void
    {
        $this->backupEnvironmentVariables();
    }

    protected function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    #[DataProvider('directoryProvider')]
    public function test_it_provides_the_project_directory(
        string|false $directory,
        bool $absolute,
        bool $readableDirectory,
        string|Exception|null $expected,
    ): void {
        if ($directory === false) {
            putenv('INFECTION_TEST_PROJECT_DIR');
        } else {
            putenv('INFECTION_TEST_PROJECT_DIR=' . $directory);
        }

        $fileSystemMock = $this->createMock(FileSystem::class);
        $fileSystemMock
            ->method('isAbsolutePath')
            ->willReturn($absolute);
        $fileSystemMock
            ->method('isReadableDirectory')
            ->willReturn($readableDirectory);

        $provider = new EnvironmentVariableBasedProjectDirectoryProvider(
            $fileSystemMock,
            'INFECTION_TEST_PROJECT_DIR',
        );

        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $actual = $provider->provide();

        if (!($expected instanceof Exception)) {
            $this->assertSame($expected, $actual);
        }
    }

    public static function directoryProvider(): iterable
    {
        yield 'env variable is not set' => [
            'directory' => false,
            'absolute' => true,
            'readableDirectory' => true,
            'expected' => null,
        ];

        yield 'nominal: absolute readable directory' => [
            'directory' => '/path/to/project',
            'absolute' => true,
            'readableDirectory' => true,
            'expected' => '/path/to/project',
        ];

        yield 'relative path' => [
            'directory' => 'relative/path',
            'absolute' => false,
            'readableDirectory' => true,
            'expected' => new InvalidArgumentException(
                'Expected the path "relative/path" to be an absolute path.',
            ),
        ];

        yield 'absolute path that is not a readable directory' => [
            'directory' => '/non-readable/path',
            'absolute' => true,
            'readableDirectory' => false,
            'expected' => new InvalidArgumentException(
                'Expected the path "/non-readable/path" to point to a readable directory.',
            ),
        ];
    }
}
