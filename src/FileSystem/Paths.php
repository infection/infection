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

use function array_map;
use Infection\CannotBeInstantiated;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class Paths
{
    use CannotBeInstantiated;

    /**
     * Resolves paths against a base directory, preserving array keys.
     *
     * By default, relative paths are joined to the base and simplified, while
     * absolute paths are kept unchanged rather than simplified as in Symfony's
     * Path::makeAbsolute().
     * For example, if /project/link points to /external/nested, preserving
     * /project/link/.. lets the filesystem follow the symbolic link before
     * resolving ".." to /external, whereas Symfony returns /project.
     *
     * @template TKey of array-key
     *
     * @param array<TKey, string> $paths
     * @param string $basePath Use an absolute base to ensure absolute results; required when $canonicalize is true.
     * @param bool $canonicalize Set to true to use Symfony's Path::makeAbsolute(), which simplifies
     *                           all paths by removing "." and resolving ".." segments without
     *                           following symbolic links.
     *
     * @return ($paths is list<string> ? list<non-empty-string> : array<TKey, non-empty-string>)
     */
    public static function makeAbsolute(
        array $paths,
        string $basePath,
        bool $canonicalize = false,
    ): array {
        return array_map(
            static fn (string $path): string => self::makePathAbsolute(
                $path,
                $basePath,
                $canonicalize,
            ),
            $paths,
        );
    }

    /**
     * @return non-empty-string
     */
    private static function makePathAbsolute(
        string $path,
        string $basePath,
        bool $canonicalize,
    ): string {
        $absolutePath = match (true) {
            $canonicalize => Path::makeAbsolute($path, $basePath),
            Path::isAbsolute($path) => $path,
            default => Path::join($basePath, $path),
        };

        Assert::stringNotEmpty(
            $absolutePath,
            'Resolving a path against its base path must not produce an empty path.',
        );

        return $absolutePath;
    }
}
