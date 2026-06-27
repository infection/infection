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

namespace Infection\Configuration;

use function class_exists;
use function ctype_upper;
use function dirname;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\FileSystem\FileSystem;
use InvalidArgumentException;
use function sprintf;
use function str_contains;
use function str_starts_with;
use Symfony\Component\Filesystem\Path;

/**
 * Classifies positional `path` arguments into source-filter and
 * test-framework-extra-args buckets.
 *
 * @internal
 */
final readonly class PositionalPathsClassifier
{
    private const string KIND_SOURCE = 'source';

    private const string KIND_TEST = 'test';

    public function __construct(
        private FileSystem $fileSystem,
    ) {
    }

    /**
     * @param list<non-empty-string> $paths
     */
    public function classify(
        array $paths,
        SchemaConfiguration $schema,
    ): ClassifiedPaths {
        if ($paths === []) {
            return new ClassifiedPaths([], []);
        }

        $configDir = dirname($schema->pathname);
        $absoluteSourceDirs = self::resolveAbsoluteSourceDirectories($schema, $configDir);

        $sourcePaths = [];
        $testPaths = [];

        foreach ($paths as $path) {
            $kind = $this->classifyPathKind($path, $absoluteSourceDirs, $configDir);

            if ($kind === self::KIND_SOURCE) {
                $sourcePaths[] = $path;
            } else {
                $testPaths[] = $path;
            }
        }

        return new ClassifiedPaths($sourcePaths, $testPaths);
    }

    /**
     * @return list<non-empty-string>
     */
    private static function resolveAbsoluteSourceDirectories(
        SchemaConfiguration $schema,
        string $configDir,
    ): array {
        $absolute = [];

        foreach ($schema->source->directories as $directory) {
            $canonical = Path::isAbsolute($directory)
                ? Path::canonicalize($directory)
                : Path::join($configDir, $directory);

            if ($canonical !== '') {
                $absolute[] = $canonical;
            }
        }

        return $absolute;
    }

    /**
     * @param list<non-empty-string> $absoluteSourceDirs
     */
    private static function isInsideSourceDirectories(
        string $path,
        array $absoluteSourceDirs,
    ): bool {
        if ($absoluteSourceDirs === []) {
            return false;
        }

        $candidate = Path::canonicalize($path);

        foreach ($absoluteSourceDirs as $sourceDir) {
            if ($candidate === $sourceDir || str_starts_with($candidate, $sourceDir . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<non-empty-string> $absoluteSourceDirs
     *
     * @return self::KIND_SOURCE|self::KIND_TEST
     */
    private function classifyPathKind(
        string $path,
        array $absoluteSourceDirs,
        string $configDir,
    ): string {
        // TODO: FQCN-style arguments (e.g. "\App\Foo" or "\App\Foo::method::45") will
        // be supported via https://github.com/infection/infection/issues/2237
        if (self::looksLikeFqcnWithOptionalMethodOrLine($path)) {
            throw new InvalidArgumentException(sprintf(
                'FQCN-style arguments like "%s" are not yet supported. See https://github.com/infection/infection/issues/2237.',
                $path,
            ));
        }

        $absolutePath = Path::isAbsolute($path)
            ? $path
            : Path::join($configDir, $path);

        $isValidPath = $this->isValidPath($absolutePath);
        $isInsideSourceDirectories = self::isInsideSourceDirectories($absolutePath, $absoluteSourceDirs);

        if ($isValidPath && !$isInsideSourceDirectories) {
            return self::KIND_TEST;
        }

        if ($isValidPath && $isInsideSourceDirectories) {
            return self::KIND_SOURCE;
        }

        // like `SomeFile` or `SomeFile.php` - bare values behave as --filter values
        if (self::looksLikeClassOrFileName($path)) {
            return self::KIND_SOURCE;
        }

        // reaching here means it's neither a valid path (source or test) nor a Class-like string, so something is wrong
        throw new InvalidArgumentException(sprintf(
            'Invalid path argument "%s": multiple paths must be passed as separate arguments.',
            $path,
        ));
    }

    /**
     * \SomeNamespace\Class
     * \SomeNamespace\Class::method
     * \SomeNamespace\Class::method::34
     * App\Foo (bare, unbackslashed)
     */
    private static function looksLikeFqcnWithOptionalMethodOrLine(string $value): bool
    {
        if (str_starts_with($value, '\\') || str_contains($value, '::')) {
            return true;
        }

        return class_exists($value);
    }

    private static function looksLikeClassOrFileName(string $value): bool
    {
        return ctype_upper($value[0]) // class and file name starts with Pascal Case (A-Z only, not digits)
            && !str_contains($value, '/')
            && !str_contains($value, '\\');
    }

    private function isValidPath(string $absolutePath): bool
    {
        return $this->fileSystem->isReadableFile($absolutePath) || $this->fileSystem->isReadableDirectory($absolutePath);
    }
}
