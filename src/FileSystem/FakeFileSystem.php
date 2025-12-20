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

namespace Infection\FileSystem;

use DomainException;
use Symfony\Component\Finder\Finder;
use Traversable;

/**
 * @internal
 */
final class FakeFileSystem extends FileSystem
{
    public function dumpFile(string $filename, $content = ''): void
    {
        throw new DomainException('Unexpected call.');
    }

    public function isReadable(string $filename): bool
    {
        throw new DomainException('Unexpected call.');
    }

    public function isReadableFile(string $filename): bool
    {
        throw new DomainException('Unexpected call.');
    }

    public function realPath(string $filename): string
    {
        throw new DomainException('Unexpected call.');
    }

    public function isReadableDirectory(string $filename): bool
    {
        throw new DomainException('Unexpected call.');
    }

    public function createFinder(): Finder
    {
        throw new DomainException('Unexpected call.');
    }

    public function copy(string $originFile, string $targetFile, bool $overwriteNewerFiles = false): never
    {
        throw new DomainException('Unexpected call.');
    }

    public function mkdir(iterable|string $dirs, int $mode = 0o777): never
    {
        throw new DomainException('Unexpected call.');
    }

    public function exists(iterable|string $files): bool
    {
        throw new DomainException('Unexpected call.');
    }

    public function touch(iterable|string $files, ?int $time = null, ?int $atime = null): never
    {
        throw new DomainException('Unexpected call.');
    }

    public function remove(iterable|string $files): never
    {
        throw new DomainException('Unexpected call.');
    }

    public function chmod(
        iterable|string $files,
        int $mode,
        int $umask = 0o000,
        bool $recursive = false,
    ): never {
        throw new DomainException('Unexpected call.');
    }

    public function chown(iterable|string $files, int|string $user, bool $recursive = false): never
    {
        throw new DomainException('Unexpected call.');
    }

    public function chgrp(iterable|string $files, int|string $group, bool $recursive = false): never
    {
        throw new DomainException('Unexpected call.');
    }

    public function rename(string $origin, string $target, bool $overwrite = false): never
    {
        throw new DomainException('Unexpected call.');
    }

    public function symlink(string $originDir, string $targetDir, bool $copyOnWindows = false): never
    {
        throw new DomainException('Unexpected call.');
    }

    public function hardlink(string $originFile, iterable|string $targetFiles): never
    {
        throw new DomainException('Unexpected call.');
    }

    public function readlink(string $path, bool $canonicalize = false): ?string
    {
        throw new DomainException('Unexpected call.');
    }

    public function makePathRelative(string $endPath, string $startPath): string
    {
        throw new DomainException('Unexpected call.');
    }

    public function mirror(
        string $originDir,
        string $targetDir,
        ?Traversable $iterator = null,
        array $options = [],
    ): never {
        throw new DomainException('Unexpected call.');
    }

    public function isAbsolutePath(string $file): bool
    {
        throw new DomainException('Unexpected call.');
    }

    public function tempnam(string $dir, string $prefix, string $suffix = ''): string
    {
        throw new DomainException('Unexpected call.');
    }

    public function appendToFile(string $filename, $content, bool $lock = false): never
    {
        throw new DomainException('Unexpected call.');
    }

    public function readFile(string $filename): string
    {
        throw new DomainException('Unexpected call.');
    }
}
