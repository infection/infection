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

namespace Infection\Source\Matcher;

use Infection\Differ\ChangedLinesRange;
use Infection\FileSystem\FileSystem;
use Infection\Git\Git;
use Infection\Source\Exception\NoSourceFound;

/**
 * @internal
 */
final class GitDiffSourceLineMatcher implements SourceLineMatcher
{
    /** @var array<string, list<ChangedLinesRange>> */
    private ?array $memoizedFilesChangedLinesMap = null;

    /**
     * @param non-empty-string $gitDiffBase
     * @param non-empty-string $gitDiffFilter
     * @param non-empty-string[] $sourceDirectories
     */
    public function __construct(
        private readonly Git $git,
        private readonly FileSystem $filesystem,
        private readonly string $gitDiffBase,
        private readonly string $gitDiffFilter,
        private readonly array $sourceDirectories,
    ) {
    }

    public function touches(string $fileRealPath, int $startLine, int $endLine): bool
    {
        foreach ($this->getChangedLinesRanges($fileRealPath) as $changedLinesRange) {
            if ($changedLinesRange->touches($startLine, $endLine)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws NoSourceFound
     *
     * @return list<ChangedLinesRange>
     */
    private function getChangedLinesRanges(string $fileRealPath): array
    {
        $this->memoizedFilesChangedLinesMap ??= $this->getFilesChangedLinesRanges();

        return $this->memoizedFilesChangedLinesMap[$fileRealPath] ?? [];
    }

    /**
     * @throws NoSourceFound
     *
     * @return array<string, list<ChangedLinesRange>>
     */
    private function getFilesChangedLinesRanges(): array
    {
        $changedLinesByRelativePaths = $this->git->getChangedLinesRangesByFileRelativePaths(
            $this->gitDiffFilter,
            $this->gitDiffBase,
            $this->sourceDirectories,
        );

        $changedLinesByAbsolutePaths = [];

        foreach ($changedLinesByRelativePaths as $relativeFilePath => $changedLines) {
            $changedLinesByAbsolutePaths[$this->filesystem->realPath($relativeFilePath)] = $changedLines;
        }

        return $changedLinesByAbsolutePaths;
    }
}
