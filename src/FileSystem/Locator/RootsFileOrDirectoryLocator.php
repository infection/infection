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

namespace Infection\FileSystem\Locator;

use function array_shift;
use function current;
use const DIRECTORY_SEPARATOR;
use function Safe\realpath;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class RootsFileOrDirectoryLocator implements Locator
{
    /** @var string[] */
    private readonly array $roots;

    /**
     * @param string[] $roots
     */
    public function __construct(array $roots, private readonly Filesystem $filesystem)
    {
        Assert::allString($roots);

        $this->roots = $roots;
    }

    public function locate(string $fileName): string
    {
        $canonicalFileName = Path::canonicalize($fileName);

        if ($this->filesystem->isAbsolutePath($canonicalFileName)) {
            if ($this->filesystem->exists($canonicalFileName)) {
                return realpath($canonicalFileName);
            }

            throw FileOrDirectoryNotFound::fromFileName($canonicalFileName, $this->roots);
        }

        foreach ($this->roots as $path) {
            $file = $path . DIRECTORY_SEPARATOR . $canonicalFileName;

            if ($this->filesystem->exists($file)) {
                return realpath($file);
            }
        }

        throw FileOrDirectoryNotFound::fromFileName($canonicalFileName, $this->roots);
    }

    public function locateOneOf(array $fileNames): string
    {
        $file = $this->innerLocateOneOf($fileNames);

        if ($file === null) {
            throw FileOrDirectoryNotFound::fromFiles($fileNames, $this->roots);
        }

        return $file;
    }

    /**
     * @param string[] $fileNames
     */
    private function innerLocateOneOf(array $fileNames): ?string
    {
        if ($fileNames === []) {
            return null;
        }

        try {
            return $this->locate(current($fileNames));
        } catch (FileOrDirectoryNotFound) {
            array_shift($fileNames);

            return $this->innerLocateOneOf($fileNames);
        }
    }
}
