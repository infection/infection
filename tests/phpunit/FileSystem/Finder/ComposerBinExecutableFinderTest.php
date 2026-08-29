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

use Infection\FileSystem\Finder\ComposerBinExecutableFinder;
use Infection\Tests\FileSystem\FileSystemTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use function Safe\chmod;
use Symfony\Component\Filesystem\Filesystem;

#[Group('integration')]
#[CoversClass(ComposerBinExecutableFinder::class)]
final class ComposerBinExecutableFinderTest extends FileSystemTestCase
{
    private Filesystem $fileSystem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileSystem = new Filesystem();
    }

    public function test_it_finds_a_windows_path_extension_candidate(): void
    {
        $candidate = $this->createCandidate('phpunit.cmd');

        $this->assertSame(
            $candidate,
            ComposerBinExecutableFinder::find(['phpunit'], $this->tmp, 'Windows', '.cmd;.exe'),
        );
    }

    #[DataProvider('provideMissingWindowsPathExtension')]
    public function test_it_uses_default_windows_path_extensions(string|false $pathExtension): void
    {
        $candidate = $this->createCandidate('phpunit.exe', 0644);

        $this->assertSame(
            $candidate,
            ComposerBinExecutableFinder::find(['phpunit'], $this->tmp, 'Windows', $pathExtension),
        );
    }

    public static function provideMissingWindowsPathExtension(): iterable
    {
        yield 'missing variable' => ['pathExtension' => false];

        yield 'empty variable' => ['pathExtension' => ''];
    }

    public function test_it_ignores_windows_path_extensions_on_other_systems(): void
    {
        $this->createCandidate('phpunit.cmd');

        $this->assertNull(
            ComposerBinExecutableFinder::find(['phpunit'], $this->tmp, 'Darwin', '.cmd'),
        );
    }

    #[RequiresOperatingSystem('Linux|Darwin')]
    public function test_it_finds_an_executable_candidate_on_other_systems(): void
    {
        $candidate = $this->createCandidate('phpunit');

        $this->assertSame(
            $candidate,
            ComposerBinExecutableFinder::find(['phpunit'], $this->tmp, 'Linux', false),
        );
    }

    private function createCandidate(string $name, int $permissions = 0755): string
    {
        $path = $this->tmp . '/' . $name;
        $this->fileSystem->dumpFile($path, '#!/usr/bin/env php');
        chmod($path, $permissions);

        return $path;
    }
}
