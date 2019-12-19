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

namespace Infection\Tests;

use Safe\substr;
use Safe\realpath;
use const DIRECTORY_SEPARATOR;
use Generator;
use function random_int;
use function realpath;
use function str_replace;
use function strrpos;
use function substr;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use function sys_get_temp_dir;

/**
 * Normalizes path. Replaces backslashes with forward ones
 */
function normalizePath(string $value): string
{
    return str_replace(DIRECTORY_SEPARATOR, '/', $value);
}

function normalizeLineReturn(string $value): string
{
    return str_replace(["\r\n", "\r"], "\n", $value);
}

function generator_to_phpunit_data_provider(iterable $source): Generator
{
    foreach ($source as $key => $value) {
        yield $key => [$value];
    }
}

/**
 * Creates a temporary directory.
 *
 * @param string $namespace the directory path in the system's temporary directory
 * @param string $className the name of the test class
 *
 * @return string The path to the created directory
 *
 * @TODO: extract the FS utils from Box if they are not going to be merged to Symfony
 */
function make_tmp_dir(string $namespace, string $className): string
{
    if (false !== ($pos = strrpos($className, '\\'))) {
        $shortClass = substr($className, $pos + 1);
    } else {
        $shortClass = $className;
    }

    // Usage of realpath() is important if the temporary directory is a
    // symlink to another directory (e.g. /var => /private/var on some Macs)
    // We want to know the real path to avoid comparison failures with
    // code that uses real paths only
    $systemTempDir = str_replace('\\', '/', realpath(sys_get_temp_dir()));
    $basePath = $systemTempDir . '/' . $namespace . '/' . $shortClass;

    $result = false;
    $attempts = 0;
    $filesystem = new Filesystem();

    do {
        $tmpDir = normalizePath($basePath . random_int(10000, 99999));

        try {
            $filesystem->mkdir($tmpDir, 0777);

            $result = true;
        } catch (IOException $exception) {
            ++$attempts;
        }
    } while (false === $result && $attempts <= 10);

    return $tmpDir;
}
