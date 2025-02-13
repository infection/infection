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

use Symfony\Component\Filesystem\Filesystem;
use Traversable;

/**
 * @internal
 */
final class DummyFileSystem extends Filesystem
{
    public function copy(string $originFile, string $targetFile, bool $overwriteNewerFiles = false): void
    {
    }

    public function mkdir($dirs, int $mode = 0777): void
    {
    }

    public function exists(string|iterable $files): bool
    {
        return false;
    }

    public function touch($files, ?int $time = null, ?int $atime = null): void
    {
    }

    public function remove($files): void
    {
    }

    public function chmod($files, int $mode, int $umask = 0000, bool $recursive = false): void
    {
    }

    public function chown($files, $user, bool $recursive = false): void
    {
    }

    public function chgrp($files, $group, bool $recursive = false): void
    {
    }

    public function rename(string $origin, string $target, bool $overwrite = false): void
    {
    }

    public function symlink(string $originDir, string $targetDir, bool $copyOnWindows = false): void
    {
    }

    public function hardlink(string $originFile, $targetFiles): void
    {
    }

    public function readlink(string $path, bool $canonicalize = false): ?string
    {
        return '';
    }

    public function makePathRelative(string $endPath, string $startPath): string
    {
        return '';
    }

    public function mirror(string $originDir, string $targetDir, ?Traversable $iterator = null, array $options = []): void
    {
    }

    public function isAbsolutePath(string $file): bool
    {
        return true;
    }

    public function tempnam(string $dir, string $prefix, string $suffix = ''): string
    {
        return '';
    }

    public function dumpFile(string $filename, $content): void
    {
    }

    public function appendToFile(string $filename, $content, bool $lock = false): void
    {
    }
}
