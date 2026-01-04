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

use function file_get_contents;
use function is_string;
use function method_exists;
use function restore_error_handler;
use function set_error_handler;
use SplFileInfo;
use Symfony\Component\Filesystem\Exception\IOException;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class FileStore
{
    /**
     * @var array<string, string>
     */
    private array $contents = [];

    public function __construct(
        private readonly FileSystem $fileSystem,
    ) {
    }

    /**
     * @throws IOException
     */
    public function getContents(SplFileInfo|string $file): string
    {
        $path = is_string($file)
            ? $file
            : $file->getRealPath();

        Assert::notFalse($path);

        return $this->contents[$path] ??= $this->readFile($path);
    }

    /**
     * @throws IOException
     */
    private function readFile(string $path): string
    {
        // @phpstan-ignore function.alreadyNarrowedType
        if (method_exists($this->fileSystem, 'readFile')) {
            return $this->fileSystem->readFile($path);
        }

        // To delete once we drop support for Symfony 6.4.
        // Copied from Symfony\Finder\SplFileInfo::getContents() with the exception adjusted
        // @phpstan-ignore argument.type
        set_error_handler(static function ($type, $msg) use (&$error): void { $error = $msg; });

        try {
            // @phpstan-ignore theCodingMachineSafe.function
            $content = file_get_contents($path);
        } finally {
            restore_error_handler();
        }

        if ($content === false) {
            throw new IOException($error ?? '');
        }

        return $content;
    }
}
