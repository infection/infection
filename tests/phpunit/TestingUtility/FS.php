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

namespace Infection\Tests\TestingUtility;

use function bin2hex;
use const DIRECTORY_SEPARATOR;
use Infection\CannotBeInstantiated;
use function random_bytes;
use function sprintf;
use function str_replace;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use function sys_get_temp_dir;
use function tempnam;

final class FS
{
    use CannotBeInstantiated;

    /**
     * Replaces the path directory separator with the system one.
     *
     * For example, on Windows:
     * 'C:/path/to/file' => 'C:\path\to\file',
     *
     * This would be more appropriate as being part of the SymfonyPath.
     */
    public static function escapePath(string $path): string
    {
        return str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Creates a temporary file with support for custom stream wrappers. Same as tempnam(),
     * but targets the system default temporary directory by default and has a more consistent
     * name with tmpDir.
     *
     * For example:
     *
     *  ```php
     *  tmpFile('build')
     *
     *  // on OSX
     *  => '/var/folders/p3/lkw0cgjj2fq0656q_9rd0mk80000gn/T/build8d9e0f1a'
     *  // on Windows
     *  => C:\Windows\Temp\build8d9e0f1a.tmp
     *  ```
     *
     * @param string $prefix the prefix of the generated temporary file name
     * @param string $suffix the suffix of the generated temporary file name
     * @param string $targetDirectory The directory where to create the temporary directory.
     *                                Defaults to the system default temporary directory.
     *
     * @throws IOException
     * @return string the new temporary file pathname
     *
     * @see tempnam()
     * @see SymfonyFileSystem::tempnam()
     * @see self::tmpDir()
     */
    public static function tmpFile(string $prefix, string $suffix = '', ?string $targetDirectory = null): string
    {
        $filesystem = new Filesystem();

        return self::escapePath(
            $filesystem->tempnam(
                $targetDirectory ?? sys_get_temp_dir(),
                $prefix,
                $suffix,
            ),
        );
    }

    /**
     * Creates a temporary directory with support for custom stream wrappers. Similar to tempnam()
     * but creates a directory instead of a file.
     *
     * For example:
     *
     * ```php
     * tmpDir('build')
     *
     * // on OSX
     * => '/var/folders/p3/lkw0cgjj2fq0656q_9rd0mk80000gn/T/build8d9e0f1a'
     * // on Windows
     * => C:\Windows\Temp\build8d9e0f1a.tmp
     * ```
     *
     * @param string $prefix te prefix of the generated temporary directory name
     * @param string $targetDirectory The directory where to create the temporary directory.
     *                                Defaults to the system default temporary directory.
     *
     * @throws IOException
     *
     * @return string the new temporary directory pathname
     *
     * @see tempnam()
     */
    public static function tmpDir(string $prefix, ?string $targetDirectory = null): string
    {
        $filesystem = new Filesystem();
        $targetDirectory ??= sys_get_temp_dir();

        for ($i = 0; $i < 10; ++$i) {
            // Create a unique directory name using the same pattern as Symfony's tempnam()
            $tmpDir = $targetDirectory . DIRECTORY_SEPARATOR . $prefix . bin2hex(random_bytes(4));

            if ($filesystem->exists($tmpDir)) {
                continue;
            }

            try {
                $filesystem->mkdir($tmpDir);

                return $tmpDir;
            } catch (IOException) {
                continue;
            }
        }

        throw new IOException(
            sprintf(
                'A temporary directory could not be created in "%s".',
                $targetDirectory,
            ),
        );
    }
}
