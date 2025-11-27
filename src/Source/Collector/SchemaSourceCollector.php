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

use Symfony\Component\Finder\Finder;

/**
 * TODO: extract the rename in a separate PR
 *
 * @internal
 */
final readonly class SchemaSourceCollector implements SourceCollector
{
    /**
     * @param string[] $sourceDirectories
     * @param string[] $excludedDirectoriesOrFiles
     */
    public function __construct(
        private array $sourceDirectories,
        private array $excludedDirectoriesOrFiles,
    ) {
    }

    // TODO: I think the file/glob based filter could be applied here directly.
    //  For performance reasons, most collectors already apply a filtering of some kind
    //  e.g. the git diff. So currently if feels we are a bit in-between for all of them:
    //  - git diff uses the sources for further filter but doesn't account for the excluded directories neither the user filter (but the git diff filter)
    //  - the schema source collector does not account for the user filter
    //  - traces don't account for either, we decorate them with the source filter
    public function collect(): iterable
    {
        if ($this->sourceDirectories === []) {
            return [];
        }

        // TODO: to use the filesystem factory method as per the PoC
        return Finder::create()
            ->in($this->sourceDirectories)
            ->exclude($this->excludedDirectoriesOrFiles)
            ->notPath($this->excludedDirectoriesOrFiles)
            ->files()
            ->name('*.php')
        ;
    }
}
