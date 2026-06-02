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
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PhpParser\NodeVisitorAbstract;
use function strtolower;

final class IoCodeDetector extends NodeVisitorAbstract
{
    // See https://www.php.net/manual/en/ref.filesystem.php
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
        'delete',
        'dir',
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
        'getcwd',
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
        'opendir',
        'parse_ini_file',
        'parse_ini_string',
        'pathinfo',
        'pclose',
        'popen',
        'readfile',
        'readlink',
        'readdir',
        'realpath_cache_get',
        'realpath_cache_size',
        'realpath',
        'rename',
        'rewind',
        'rewinddir',
        'rmdir',
        'scandir',
        'set_file_buffer',
        'stat',
        'symlink',
        'tempnam',
        'tmpfile',
        'touch',
        'umask',
        'unlink',
    ];

    /**
     * @var array<class-string>
     */
    private const array TEST_CASE_FILE_SYSTEM_CLASSES = [
        FileSystem::class,
        \Symfony\Component\Filesystem\Filesystem::class,
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
            $this->testCaseFileSystemClasses[$fileSystemClass] = true;
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

        $className = $new->class->toString();

        if ($new->class->isFullyQualified()) {
            return array_key_exists($className, $this->testCaseFileSystemClasses);
        }

        $classNameParts = $new->class->getParts();
        $importedClassName = $this->classImports[$classNameParts[0]] ?? null;

        if ($importedClassName === null) {
            return false;
        }

        $resolvedClassName = count($classNameParts) === 1
            ? $importedClassName
            : $importedClassName . '\\' . implode('\\', array_slice($classNameParts, 1));

        return array_key_exists($resolvedClassName, $this->testCaseFileSystemClasses);
    }

    private function getUsedName(UseItem $useItem, ?Name $prefix): string
    {
        if ($prefix === null) {
            return $useItem->name->toString();
        }

        return $prefix->toString() . '\\' . $useItem->name->toString();
    }
}
