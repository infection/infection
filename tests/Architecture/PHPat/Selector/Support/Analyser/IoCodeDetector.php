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

namespace Infection\Tests\Architecture\PHPat\Selector\Support\Analyser;

use function array_key_exists;
use function array_slice;
use function count;
use function explode;
use function implode;
use Infection\FileSystem\FileSystem;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PhpParser\NodeVisitorAbstract;
use function strtolower;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use XMLReader;
use XMLWriter;

final class IoCodeDetector extends NodeVisitorAbstract
{
    // See https://www.php.net/manual/en/ref.filesystem.php and newer PHP migration guides.
    private const array NATIVE_FUNCTIONS = [
        'basename',
        'chdir',
        'chgrp',
        'chmod',
        'chown',
        'chroot',
        'clearstatcache',
        'closedir',
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
        'readfile',
        'readlink',
        'readdir',
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

    /**
     * @var array<class-string, non-empty-list<string>>
     */
    private const array IO_STATIC_METHODS = [
        XMLReader::class => [
            'fromStream',
            'fromUri',
            'open',
        ],
        XMLWriter::class => [
            'openUri',
            'toStream',
            'toUri',
        ],
    ];

    /**
     * @var array<class-string>
     */
    private const array TEST_CASE_FILE_SYSTEM_CLASSES = [
        FileSystem::class,
        SymfonyFilesystem::class,
    ];

    /**
     * @var array<string, true>
     */
    private array $nativeFunctions = [];

    /**
     * @var array<string, true>
     */
    private array $testCaseFileSystemClasses = [];

    /**
     * @var array<string, array<string, true>>
     */
    private array $ioStaticMethods = [];

    /**
     * @var array<string, string>
     */
    private array $classImports = [];

    private bool $hasIoOperations = false;

    public function __construct(
        private readonly bool $testCaseCode,
    ) {
        foreach (self::NATIVE_FUNCTIONS as $nativeFunction) {
            $this->nativeFunctions[strtolower($nativeFunction)] = true;
        }

        foreach (self::TEST_CASE_FILE_SYSTEM_CLASSES as $fileSystemClass) {
            $this->testCaseFileSystemClasses[strtolower($fileSystemClass)] = true;
        }

        foreach (self::IO_STATIC_METHODS as $className => $methodNames) {
            foreach ($methodNames as $methodName) {
                $this->ioStaticMethods[strtolower($className)][strtolower($methodName)] = true;
            }
        }
    }

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof Use_) {
            return $this->detectUseStatement($node->uses, $node->type, null);
        }

        if ($node instanceof GroupUse) {
            return $this->detectUseStatement($node->uses, $node->type, $node->prefix);
        }

        if ($node instanceof FuncCall && $this->isIoFunctionCall($node)) {
            $this->hasIoOperations = true;
        }

        if ($node instanceof StaticCall && $this->isIoStaticCall($node)) {
            $this->hasIoOperations = true;
        }

        if ($node instanceof New_ && $this->isFileSystemInstantiation($node)) {
            $this->hasIoOperations = true;
        }

        return null;
    }

    public function hasIoOperations(): bool
    {
        return $this->hasIoOperations;
    }

    /**
     * @param UseItem[] $useItems
     */
    private function detectUseStatement(array $useItems, int $defaultType, ?Name $prefix): null
    {
        foreach ($useItems as $useItem) {
            $type = $useItem->type === Use_::TYPE_UNKNOWN ? $defaultType : $useItem->type;
            $usedName = $this->getUsedName($useItem, $prefix);

            if ($type === Use_::TYPE_FUNCTION && $this->isIoFunctionName($usedName)) {
                $this->hasIoOperations = true;

                return null;
            }

            if ($type === Use_::TYPE_NORMAL) {
                $this->classImports[$useItem->getAlias()->toString()] = $usedName;
            }
        }

        return null;
    }

    private function isIoFunctionCall(FuncCall $funcCall): bool
    {
        return $funcCall->name instanceof Name
            && $funcCall->name->isFullyQualified()
            && $this->isIoFunctionName($funcCall->name->toString());
    }

    private function isIoFunctionName(string $functionName): bool
    {
        $nameParts = explode('\\', strtolower($functionName));

        if (count($nameParts) === 1) {
            return array_key_exists($nameParts[0], $this->nativeFunctions);
        }

        return count($nameParts) === 2
            && $nameParts[0] === 'safe'
            && array_key_exists($nameParts[1], $this->nativeFunctions);
    }

    private function isFileSystemInstantiation(New_ $new): bool
    {
        if (!$this->testCaseCode || !$new->class instanceof Name) {
            return false;
        }

        $className = $this->resolveClassName($new->class);

        return $className !== null
            && array_key_exists(strtolower($className), $this->testCaseFileSystemClasses);
    }

    private function isIoStaticCall(StaticCall $staticCall): bool
    {
        if (!$staticCall->class instanceof Name || !$staticCall->name instanceof Identifier) {
            return false;
        }

        $className = $this->resolveClassName($staticCall->class);

        if ($className === null) {
            return false;
        }

        $methodNames = $this->ioStaticMethods[strtolower($className)] ?? null;

        if ($methodNames === null) {
            return false;
        }

        return array_key_exists(strtolower($staticCall->name->toString()), $methodNames);
    }

    private function resolveClassName(Name $className): ?string
    {
        if ($className->isFullyQualified()) {
            return $className->toString();
        }

        $classNameParts = $className->getParts();
        $importedClassName = $this->classImports[$classNameParts[0]] ?? null;

        if ($importedClassName === null) {
            return null;
        }

        return count($classNameParts) === 1
            ? $importedClassName
            : $importedClassName . '\\' . implode('\\', array_slice($classNameParts, 1));
    }

    private function getUsedName(UseItem $useItem, ?Name $prefix): string
    {
        if ($prefix === null) {
            return $useItem->name->toString();
        }

        return $prefix->toString() . '\\' . $useItem->name->toString();
    }
}
