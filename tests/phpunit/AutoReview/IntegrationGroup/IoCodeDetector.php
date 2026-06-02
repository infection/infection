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
use function file_exists;
use Infection\CannotBeInstantiated;
use function Safe\file_get_contents;
use function Safe\preg_match_all;
use function sprintf;
use function str_contains;
use Symfony\Component\Finder\Finder;

final class IoCodeDetector
{
    use CannotBeInstantiated;

    // See https://www.php.net/manual/en/ref.filesystem.php and newer PHP migration guides.
    private const array NATIVE_FUNCTIONS = [
        'basename',
        'chgrp',
        'chmod',
        'chown',
        'clearstatcache',
        'copy',
        'curl_close',
        'curl_copy_handle',
        'curl_errno',
        'curl_error',
        'curl_escape',
        'curl_exec',
        'curl_file_create',
        'curl_getinfo',
        'curl_init',
        'curl_multi_add_handle',
        'curl_multi_close',
        'curl_multi_errno',
        'curl_multi_exec',
        'curl_multi_getcontent',
        'curl_multi_get_handles',
        'curl_multi_info_read',
        'curl_multi_init',
        'curl_multi_remove_handle',
        'curl_multi_select',
        'curl_multi_setopt',
        'curl_multi_strerror',
        'curl_pause',
        'curl_reset',
        'curl_setopt',
        'curl_setopt_array',
        'curl_share_close',
        'curl_share_errno',
        'curl_share_init',
        'curl_share_init_persistent',
        'curl_share_setopt',
        'curl_share_strerror',
        'curl_strerror',
        'curl_unescape',
        'curl_upkeep',
        'curl_version',
        'delete',
        'chdir',
        'chroot',
        'closedir',
        'dir',
        'dirname',
        'disk_free_space',
        'disk_total_space',
        'diskfreespace',
        'fclose',
        'fdatasync',
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
        'fsync',
        'ftell',
        'ftruncate',
        'fwrite',
        'finfo_file',
        'getcwd',
        'get_headers',
        'get_meta_tags',
        'glob',
        'gzfile',
        'header',
        'header_remove',
        'headers_list',
        'headers_sent',
        'hash_file',
        'hash_hmac_file',
        'hash_update_file',
        'highlight_file',
        'http_clear_last_response_headers',
        'http_get_last_response_headers',
        'http_response_code',
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
        'md5_file',
        'mkdir',
        'move_uploaded_file',
        'opcache_compile_file',
        'opcache_invalidate',
        'opcache_is_script_cached',
        'opcache_is_script_cached_in_file_cache',
        'opendir',
        'parse_ini_file',
        'parse_ini_string',
        'pathinfo',
        'pclose',
        'popen',
        'posix_access',
        'posix_eaccess',
        'posix_fpathconf',
        'posix_getcwd',
        'posix_isatty',
        'posix_mkfifo',
        'posix_mknod',
        'posix_pathconf',
        'posix_ttyname',
        'readgzfile',
        'readdir',
        'readfile',
        'readlink',
        'realpath_cache_get',
        'realpath_cache_size',
        'realpath',
        'rename',
        'request_parse_body',
        'rewind',
        'rewinddir',
        'rmdir',
        'scandir',
        'set_file_buffer',
        'setcookie',
        'setrawcookie',
        'sha1_file',
        'simplexml_load_file',
        'socket_atmark',
        'stat',
        'stream_bucket_append',
        'stream_bucket_make_writeable',
        'stream_bucket_new',
        'stream_bucket_prepend',
        'stream_context_create',
        'stream_context_get_default',
        'stream_context_get_options',
        'stream_context_get_params',
        'stream_context_set_default',
        'stream_context_set_options',
        'stream_context_set_option',
        'stream_context_set_params',
        'stream_copy_to_stream',
        'stream_filter_append',
        'stream_filter_prepend',
        'stream_filter_register',
        'stream_filter_remove',
        'stream_get_contents',
        'stream_get_filters',
        'stream_get_line',
        'stream_get_meta_data',
        'stream_get_transports',
        'stream_get_wrappers',
        'stream_is_local',
        'stream_isatty',
        'stream_register_wrapper',
        'stream_resolve_include_path',
        'stream_select',
        'stream_set_blocking',
        'stream_set_chunk_size',
        'stream_set_read_buffer',
        'stream_set_timeout',
        'stream_set_write_buffer',
        'stream_socket_accept',
        'stream_socket_client',
        'stream_socket_enable_crypto',
        'stream_socket_get_name',
        'stream_socket_pair',
        'stream_socket_recvfrom',
        'stream_socket_sendto',
        'stream_socket_server',
        'stream_socket_shutdown',
        'stream_supports_lock',
        'stream_wrapper_register',
        'stream_wrapper_restore',
        'stream_wrapper_unregister',
        'symlink',
        'tempnam',
        'tmpfile',
        'touch',
        'umask',
        'unlink',
        'xmlwriter_flush',
        'xmlwriter_open_uri',
    ];

    private const array ARBITRARY_STATEMENTS = [
        'XMLReader::open(',
        'XMLReader::fromStream(',
        'XMLReader::fromUri(',
        'XMLWriter::openUri(',
        'XMLWriter::toStream(',
        'XMLWriter::toUri(',
        'use Symfony\Component\Filesystem\Filesystem;',
    ];

    /**
     * @var string[]|null
     */
    private static ?array $statements = null;

    public static function codeContainsIoOperations(string $code): bool
    {
        foreach (self::getStatements() as $statement) {
            if (str_contains($code, $statement)) {
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
            self::$statements[] = sprintf('use function %s', $safeFunctionName);
            self::$statements[] = sprintf('\\%s(', $safeFunctionName);
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
        $safeFilesystemFiles = [];

        $phpDirectories = Finder::create()
            ->directories()
            ->in(__DIR__ . '/../../../../vendor/thecodingmachine/safe/generated')
            ->depth(0);

        foreach ($phpDirectories as $phpDirectory) {
            $safeFilesystemFiles[] = sprintf('%s/filesystem.php', $phpDirectory->getPathname());
            $safeFilesystemFiles[] = sprintf('%s/dir.php', $phpDirectory->getPathname());
        }

        foreach ($safeFilesystemFiles as $filePath) {
            if (!file_exists($filePath)) {
                continue;
            }

            preg_match_all(
                '/function (?<function_name>[_\p{L}]+)\(/u',
                file_get_contents($filePath),
                $matches,
            );

            foreach ($matches['function_name'] as $functionName) {
                $functionNames[$functionName] = null;
            }
        }

        return array_keys($functionNames);
    }
}
