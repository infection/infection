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

use function array_key_exists;
use function array_keys;
use function dirname;
use DomainException;
use function is_string;
use Override;
use function sprintf;
use function str_starts_with;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Traversable;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class InMemoryFileSystem extends FileSystem
{
    /**
     * @var array<string, string>
     */
    private array $files = [];

    /**
     * @var array<string, string>
     */
    private array $canonicalFileNames = [];

    /**
     * @var array<string, true>
     */
    private array $directories = [];

    #[Override]
    public function dumpFile(string $filename, $content = ''): void
    {
        Assert::stringNotEmpty($content);
        $this->assertDirectoryDoesNotExist($filename);

        $this->files[$filename] = $content;
        $this->canonicalFileNames[$filename] = Path::canonicalize($filename);
    }

    public function isReadable(string $filename): bool
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function isReadableFile(string $filename): bool
    {
        return array_key_exists($filename, $this->files);
    }

    #[Override]
    public function realPath(string $filename): string
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function isReadableDirectory(string $filename): bool
    {
        $canonicalDirectory = Path::canonicalize($filename);

        if (array_key_exists($canonicalDirectory, $this->directories)) {
            return true;
        }

        foreach ($this->canonicalFileNames as $file) {
            if (self::isParentDirectory($canonicalDirectory, $file)) {
                return true;
            }
        }

        return false;
    }

    #[Override]
    public function createFinder(): Finder
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function copy(string $originFile, string $targetFile, bool $overwriteNewerFiles = false): never
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function mkdir(iterable|string $dirs, int $mode = 0o777): void
    {
        $dirs = is_string($dirs) ? [$dirs] : $dirs;

        foreach ($dirs as $dir) {
            $canonicalDirectory = Path::canonicalize($dir);

            $this->assertFileDoesNotExist($dir);

            $this->directories[$canonicalDirectory] = true;
        }
    }

    #[Override]
    public function exists(iterable|string $files): bool
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function touch(iterable|string $files, ?int $time = null, ?int $atime = null): never
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function remove(iterable|string $files): never
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function chmod(
        iterable|string $files,
        int $mode,
        int $umask = 0o000,
        bool $recursive = false,
    ): never {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function chown(iterable|string $files, int|string $user, bool $recursive = false): never
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function chgrp(iterable|string $files, int|string $group, bool $recursive = false): never
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function rename(string $origin, string $target, bool $overwrite = false): never
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function symlink(string $originDir, string $targetDir, bool $copyOnWindows = false): never
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function hardlink(string $originFile, iterable|string $targetFiles): never
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function readlink(string $path, bool $canonicalize = false): ?string
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function makePathRelative(string $endPath, string $startPath): string
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function mirror(
        string $originDir,
        string $targetDir,
        ?Traversable $iterator = null,
        array $options = [],
    ): never {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function isAbsolutePath(string $file): bool
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function tempnam(string $dir, string $prefix, string $suffix = ''): string
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function appendToFile(string $filename, $content, bool $lock = false): never
    {
        throw new DomainException('Unexpected call.');
    }

    #[Override]
    public function readFile(string $filename): string
    {
        if (!array_key_exists($filename, $this->files)) {
            throw new IOException(
                sprintf(
                    'Failed to read file "%s": File is a directory.',
                    $filename,
                ),
            );
        }

        return $this->files[$filename];
    }

    private function assertDirectoryDoesNotExist(string $filename): void
    {
        $canonicalFilename = Path::canonicalize($filename);

        if (array_key_exists($canonicalFilename, $this->directories)) {
            throw new DomainException(
                sprintf(
                    'Cannot dump file "%s": a directory exists at the same path.',
                    $filename,
                ),
            );
        }
    }

    private function assertFileDoesNotExist(string $canonicalDirName): void
    {
        foreach (array_keys($this->files) as $filename) {
            if ($filename === $canonicalDirName) {
                throw new DomainException(
                    sprintf(
                        'Cannot create directory "%s": a file exists at the same path.',
                        $canonicalDirName,
                    ),
                );
            }
        }
    }

    private static function isParentDirectory(string $directory, string $file): bool
    {
        $fileDirectory = dirname($file);

        return $directory === $fileDirectory
            || str_starts_with($fileDirectory, $directory . '/');
    }
}
