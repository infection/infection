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

namespace Infection\Source\Collector;

use function array_map;
use function dirname;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class SourceFileCollector
{
    /**
     * @param non-empty-string[] $sourceDirectories
     * @param non-empty-string[] $excludedFilesOrDirectories
     */
    public function __construct(
        private array $sourceDirectories,
        private array $excludedFilesOrDirectories,
    ) {
    }

    /**
     * @return iterable<SplFileInfo>
     */
    public function collect(): iterable
    {
        if ($this->sourceDirectories === []) {
            return [];
        }

        return Finder::create()
            ->in($this->sourceDirectories)
            ->exclude($this->excludedFilesOrDirectories)
            ->notPath($this->excludedFilesOrDirectories)
            ->files()
            ->name('*.php');
    }

    /**
     * @param non-empty-string $configurationPathname
     * @param non-empty-string[] $sourceDirectories
     * @param non-empty-string[] $excludedFilesOrDirectories
     */
    public static function create(
        string $configurationPathname,
        array $sourceDirectories,
        array $excludedFilesOrDirectories,
    ): self {
        $configurationDirname = dirname($configurationPathname);

        $mapToAbsolutePath = static fn (string $path) => Path::isAbsolute($path)
            ? $path
            : Path::join(
                $configurationDirname,
                $path,
            );

        return new self(
            // We need to make the source file paths absolute, otherwise the
            // collector will collect the files relative to the current working
            // directory instead of relative to the location of the configuration
            // file.
            array_map(
                $mapToAbsolutePath(...),
                $sourceDirectories,
            ),
            $excludedFilesOrDirectories,
        );
    }
}
