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

use Infection\CannotBeInstantiated;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\SplFileInfo as SymfonyFinderSplFileInfo;

final class SplFileInfoFactory
{
    use CannotBeInstantiated;

    public static function fromPath(string $path, string $basePath): SymfonyFinderSplFileInfo
    {
        return self::create(
            new SplFileInfo($path),
            $basePath,
        );
    }

    public static function create(
        SplFileInfo $splFileInfo,
        string $basePath,
    ): SymfonyFinderSplFileInfo {
        $realPath = $splFileInfo->getRealPath();

        // If no base path provided, use the directory of the file as a base
        if ($basePath === '') {
            $basePath = $splFileInfo->getPath();
            $relativePath = '';
            $relativePathname = $splFileInfo->getFilename();
        } else {
            // Calculate relative paths from the base path using Symfony Path
            $canonicalBasePath = Path::canonicalize($basePath);
            $canonicalFilePath = Path::canonicalize($splFileInfo->getPath());
            $canonicalRealPath = Path::canonicalize($realPath);

            $relativePath = Path::makeRelative($canonicalFilePath, $canonicalBasePath);
            $relativePathname = Path::makeRelative($canonicalRealPath, $canonicalBasePath);

            // Ensure an empty relative path is handled correctly
            if ($relativePath === '.') {
                $relativePath = '';
            }
        }

        return new SymfonyFinderSplFileInfo(
            $realPath,
            $relativePath,
            $relativePathname,
        );
    }
}
