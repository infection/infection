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

namespace Infection\Tests\AutoReview\IntegrationGroup;

use function array_keys;
use function Safe\file_get_contents;
use function Safe\preg_match_all;
use function Safe\sprintf;
use function strpos;

final class IoCodeDetector
{
    // See https://www.php.net/manual/en/ref.filesystem.php
    private const NATIVE_FUNCTIONS = [
        'basename',
        'chgrp',
        'chmod',
        'chown',
        'clearstatcache',
        'copy',
        'delete',
        'dirname',
        'disk_free_space',
        'disk_total_space',
        'diskfreespace',
        'fclose',
        'feof',
        'fflush',
        'fgetc',
        'fgetcsv',
        'fgets',
        'fgetss',
        'file_exists',
        'file_get_contents',
        'file_put_contents',
        'file',
        'fileatime',
        'filectime',
        'filegroup',
        'fileinode',
        'filemtime',
        'fileowner',
        'fileperms',
        'filesize',
        'filetype',
        'flock',
        'fnmatch',
        'fopen',
        'fpassthru',
        'fputcsv',
        'fputs',
        'fread',
        'fscanf',
        'fseek',
        'fstat',
        'ftell',
        'ftruncate',
        'fwrite',
        'glob',
        'is_dir',
        'is_executable',
        'is_file',
        'is_link',
        'is_readable',
        'is_uploaded_file',
        'is_writable',
        'is_writeable',
        'lchgrp',
        'lchown',
        'link',
        'linkinfo',
        'lstat',
        'mkdir',
        'move_uploaded_file',
        'parse_ini_file',
        'parse_ini_string',
        'pathinfo',
        'pclose',
        'popen',
        'readfile',
        'readlink',
        'realpath_cache_get',
        'realpath_cache_size',
        'realpath',
        'rename',
        'rewind',
        'rmdir',
        'set_file_buffer',
        'stat',
        'symlink',
        'tempnam',
        'tmpfile',
        'touch',
        'umask',
        'unlink',
    ];

    private const ARBITRARY_STATEMENTS = [
        'use Symfony\Component\Filesystem\Filesystem;',
    ];

    private const SAFE_FILESYSTEM_FILES = [
        __DIR__ . '/../../../../vendor/thecodingmachine/safe/generated/filesystem.php',
        __DIR__ . '/../../../../vendor/thecodingmachine/safe/generated/dir.php',
    ];

    /**
     * @var string[]|null
     */
    private static $statements;

    private function __construct()
    {
    }

    public static function codeContainsIoOperations(string $code): bool
    {
        foreach (self::getStatements() as $statement) {
            if (strpos($code, $statement) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private static function getStatements(): array
    {
        if (self::$statements !== null) {
            return self::$statements;
        }

        foreach (self::NATIVE_FUNCTIONS as $safeFunctionName) {
            self::$statements[] = sprintf('use function Safe\\%s', $safeFunctionName);
            self::$statements[] = sprintf('\\Safe\\%s(', $safeFunctionName);
        }

        foreach (self::retrieveSafeFileSystemFunctions() as $safeFunctionName) {
            self::$statements[] = sprintf('use function Safe\\%s', $safeFunctionName);
            self::$statements[] = sprintf('\\Safe\\%s(', $safeFunctionName);
        }

        foreach (self::ARBITRARY_STATEMENTS as $statement) {
            self::$statements[] = $statement;
        }

        return self::$statements;
    }

    /**
     * @return string[]
     */
    private static function retrieveSafeFileSystemFunctions(): array
    {
        $functionNames = [];

        foreach (self::SAFE_FILESYSTEM_FILES as $filePath) {
            preg_match_all(
                '/function (?<function_name>[_\p{L}]+)\(/u',
                file_get_contents($filePath),
                $matches
            );

            foreach ($matches['function_name'] as $functionName) {
                $functionNames[$functionName] = null;
            }
        }

        return array_keys($functionNames);
    }
}
