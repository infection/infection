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

namespace Infection\TestFramework\Coverage;

use function array_key_exists;
use Infection\FileSystem\FileFilter;
use function Pipeline\take;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\Assert\Assert;

/**
 * We need to remove traces that are not in the list of source files,
 * which could have files that were been directly specified. All the
 * while later we may need a list of files that in the list but were
 * not covered.
 *
 * On the other hand we don't need to filter traces all over again as
 * we're checking them against pre-filtered list of files.
 *
 * @internal
 * @final
 */
class BufferedSourceFileFilter implements FileFilter
{
    /**
     * An associative array mapping real paths to SplFileInfo objects.
     *
     * @var array<string, SplFileInfo>
     */
    private array $sourceFiles = [];

    /**
     * @param iterable<SplFileInfo> $sourceFiles
     */
    public function __construct(
        private readonly FileFilter $filter,
        iterable $sourceFiles,
    ) {
        // Make a map of source files so we can check covered files against it.
        // We don't filter here on the assumption that hash table lookups are faster.
        foreach ($sourceFiles as $sourceFile) {
            $this->sourceFiles[(string) $sourceFile->getRealPath()] = $sourceFile;
        }
    }

    public function filter(iterable $input): iterable
    {
        return take($this->filter->filter($input))
            ->filter(function (Trace $trace): bool {
                $traceRealPath = $trace->getSourceFileInfo()->getRealPath();

                Assert::string($traceRealPath);

                if (array_key_exists($traceRealPath, $this->sourceFiles)) {
                    unset($this->sourceFiles[$traceRealPath]);

                    return true;
                }

                return false;
            });
    }

    /**
     * Returns files that are in source.directories from infection.json.dist but not in coverage report (phpunit's filter.whitelist.directory)
     *
     * @return iterable<SplFileInfo>
     */
    public function getUnseenInCoverageReportFiles(): iterable
    {
        /** @var iterable<SplFileInfo> $result */
        $result = $this->filter->filter($this->sourceFiles);

        return $result;
    }
}
