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

namespace Infection\Tests\FileSystem\Finder;

use const DIRECTORY_SEPARATOR;
use Infection\FileSystem\Finder\ConcreteComposerExecutableFinder;
use Infection\FileSystem\Finder\Exception\FinderException;
use Infection\Tests\FileSystem\FileSystemTestCase;
use const PHP_BINARY;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily;
use PHPUnit\Framework\Attributes\WithEnvironmentVariable;
use function Safe\chdir;
use function Safe\chmod;
use function Safe\file_put_contents;
use function Safe\mkdir;
use function Safe\putenv;
use function Safe\realpath;

#[Group('integration')]
#[CoversClass(ConcreteComposerExecutableFinder::class)]
#[WithEnvironmentVariable('PATH', '')]
final class ConcreteComposerExecutableFinderTest extends FileSystemTestCase
{
    private const int EXECUTABLE_PERMISSIONS = 0755;

    private const int NON_EXECUTABLE_PERMISSIONS = 0644;

    private ConcreteComposerExecutableFinder $finder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = new ConcreteComposerExecutableFinder();
    }

    #[DataProvider('provideComposerScripts')]
    #[RequiresOperatingSystem('Linux|Darwin')]
    public function test_it_finds_composer(
        string $fileName,
        int $permissions,
        bool $expectedPhpPrefix,
    ): void {
        $composerPath = $this->createComposerScript($fileName, $permissions);

        $expected = $expectedPhpPrefix ? [PHP_BINARY, $composerPath] : [$composerPath];
        $actual = $this->finder->find();

        $this->assertSame($expected, $actual);
    }

    public static function provideComposerScripts(): iterable
    {
        yield 'executable Composer script' => [
            'fileName' => 'composer',
            'permissions' => self::EXECUTABLE_PERMISSIONS,
            'expectedPhpPrefix' => false,
        ];

        yield 'executable Composer PHAR' => [
            'fileName' => 'composer.phar',
            'permissions' => self::EXECUTABLE_PERMISSIONS,
            'expectedPhpPrefix' => true,
        ];

        yield 'non-executable Composer script' => [
            'fileName' => 'composer',
            'permissions' => self::NON_EXECUTABLE_PERMISSIONS,
            'expectedPhpPrefix' => true,
        ];
    }

    public function test_it_prefers_the_composer_script_over_the_composer_phar(): void
    {
        $composerPath = $this->createComposerScript('composer', self::EXECUTABLE_PERMISSIONS);
        $this->createComposerScript('composer.phar', self::EXECUTABLE_PERMISSIONS);

        $this->assertSame([$composerPath], $this->finder->find());
    }

    #[DataProvider('provideWindowsComposerScripts')]
    #[RequiresOperatingSystemFamily('Windows')]
    #[WithEnvironmentVariable('PATHEXT', '.bat')]
    public function test_it_finds_composer_on_windows(string $fileName, bool $expectedPhpPrefix): void
    {
        putenv('PATH=' . $this->tmp);

        $composerPath = $this->tmp . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($composerPath, '@echo off');

        $expected = $expectedPhpPrefix ? [PHP_BINARY, $composerPath] : [$composerPath];
        $actual = $this->finder->find();

        $this->assertSame($expected, $actual);
    }

    public static function provideWindowsComposerScripts(): iterable
    {
        yield 'extensionless script' => [
            'fileName' => 'composer',
            'expectedPhpPrefix' => false,
        ];

        yield 'PHAR' => [
            'fileName' => 'composer.phar',
            'expectedPhpPrefix' => true,
        ];

        yield 'batch file' => [
            'fileName' => 'composer.bat',
            'expectedPhpPrefix' => false,
        ];
    }

    public function test_it_finds_a_non_executable_directory_as_a_composer_script(): void
    {
        chdir($this->tmp);

        $composerDirectory = $this->tmp . '/composer';
        mkdir($composerDirectory);
        chmod($composerDirectory, self::NON_EXECUTABLE_PERMISSIONS);
        putenv('PATH=' . $this->tmp);

        $actual = $this->finder->find();

        $this->assertSame([PHP_BINARY, $composerDirectory], $actual);
    }

    #[DataProvider('provideNearbyDirectories')]
    public function test_it_finds_composer_in_a_nearby_directory(string $relativeComposerDirectory): void
    {
        $workingDirectory = $this->tmp . '/project/subdirectory';
        mkdir($workingDirectory, self::EXECUTABLE_PERMISSIONS, true);

        $emptyPath = $this->tmp . '/empty-path';
        mkdir($emptyPath);
        putenv('PATH=' . $emptyPath);

        $composerDirectory = realpath($workingDirectory . '/' . $relativeComposerDirectory);
        $composerPath = $this->createComposerScript(
            'composer',
            self::EXECUTABLE_PERMISSIONS,
            $composerDirectory,
        );
        chdir($workingDirectory);
        putenv('PATH=' . $emptyPath);

        $expected = [$composerPath];
        $actual = $this->finder->find();

        $this->assertSame($expected, $actual);
    }

    public static function provideNearbyDirectories(): iterable
    {
        yield 'working directory' => ['relativeComposerDirectory' => '.'];

        yield 'parent directory' => ['relativeComposerDirectory' => '..'];

        yield 'grandparent directory' => ['relativeComposerDirectory' => '../..'];
    }

    public function test_it_throws_when_composer_cannot_be_found(): void
    {
        putenv('PATH=' . $this->tmp);

        $this->expectExceptionObject(
            new FinderException(
                'Unable to locate a Composer executable on local system. Ensure that Composer is installed and available.',
            ),
        );

        $this->finder->find();
    }

    private function createComposerScript(
        string $fileName,
        int $permissions,
        ?string $directory = null,
    ): string {
        $directory ??= $this->tmp;

        chdir($directory);
        putenv('PATH=' . $this->tmp);

        $path = $directory . '/' . $fileName;
        file_put_contents($path, '#!/usr/bin/env php');
        chmod($path, $permissions);

        return $path;
    }
}
