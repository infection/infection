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

namespace Infection\Resource\Processor;

use function defined;
use function extension_loaded;
use const FILTER_VALIDATE_INT;
use function filter_var;
use function function_exists;
use function is_int;
use function is_readable;
use function Safe\file_get_contents;
use function Safe\shell_exec;
use function substr_count;
use function trim;

/**
 * @internal
 */
final class CpuCoresCountProvider
{
    /**
     * Copied and adapter from Psalm project: https://github.com/vimeo/psalm/blob/4.x/src/Psalm/Internal/Analyzer/ProjectAnalyzer.php#L1454
     */
    public static function provide(): int
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            return 1;
        }

        if (!extension_loaded('pcntl') || !function_exists('shell_exec')) {
            return 1;
        }

        // for Linux
        $hasNproc = trim(@shell_exec('command -v nproc'));

        if ($hasNproc !== '') {
            $nproc = trim(shell_exec('nproc'));
            $cpuCount = filter_var($nproc, FILTER_VALIDATE_INT);

            if (is_int($cpuCount)) {
                return $cpuCount;
            }
        }

        // for MacOS
        $ncpu = trim(shell_exec('sysctl -n hw.ncpu'));
        $cpuCount = filter_var($ncpu, FILTER_VALIDATE_INT);

        if (is_int($cpuCount)) {
            return $cpuCount;
        }

        if (is_readable('/proc/cpuinfo')) {
            $cpuInfo = file_get_contents('/proc/cpuinfo');
            $cpuCount = substr_count($cpuInfo, 'processor');

            if ($cpuCount > 0) {
                return $cpuCount;
            }
        }

        return 1;
    }
}
